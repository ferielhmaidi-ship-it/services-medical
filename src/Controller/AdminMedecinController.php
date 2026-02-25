<?php
// src/Controller/AdminMedecinController.php

namespace App\Controller;

use App\Entity\Medecin;
use App\Form\MedecinEditType;
use App\Constants\Specialty;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\DoctorVerificationService;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Mpdf\Mpdf;

#[Route('/admin/medecin')]
#[IsGranted('ROLE_ADMIN')]
class AdminMedecinController extends AbstractController
{
    // ── Column letter map for PhpSpreadsheet 2.x (no getCellByColumnAndRow) ──
    private const COL = ['A','B','C','D','E','F','G','H','I','J'];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private DoctorVerificationService   $doctorVerificationService    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/', name: 'admin_medecin_index', methods: ['GET'])]
    public function index(
        Request $request,
        MedecinRepository $medecinRepository,
        PaginatorInterface $paginator
    ): Response {
        $search    = $request->query->get('search',    '');
        $status    = $request->query->get('status',    '');
        $verified  = $request->query->get('verified',  '');
        $specialty = $request->query->get('specialty', '');

        $pagination = $paginator->paginate(
            $medecinRepository->createFilteredQueryBuilder($search, $status, $verified, $specialty),
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('admin_medecin/index.html.twig', [
            'pagination'  => $pagination,
            'specialties' => Specialty::getChoices(),
            'search'      => $search,
            'status'      => $status,
            'verified'    => $verified,
            'specialty'   => $specialty,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT — EXCEL
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/export/excel', name: 'admin_medecin_export_excel', methods: ['GET'])]
    public function exportExcel(
        Request $request,
        MedecinRepository $medecinRepository,
        PaginatorInterface $paginator
    ): StreamedResponse {
        $search    = $request->query->get('search',    '');
        $status    = $request->query->get('status',    '');
        $verified  = $request->query->get('verified',  '');
        $specialty = $request->query->get('specialty', '');
        $page      = $request->query->getInt('page', 1);

        $pagination = $paginator->paginate(
            $medecinRepository->createFilteredQueryBuilder($search, $status, $verified, $specialty),
            $page, 15
        );
        /** @var Medecin[] $medecins */
        $medecins = iterator_to_array($pagination->getItems());

        // ── Build spreadsheet ─────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Médecins');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1967D2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            'alignment' => ['vertical'   => Alignment::VERTICAL_CENTER],
        ];
        $altStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F8FF']],
        ];

        // ── Headers (PhpSpreadsheet 2.x uses coordinate strings) ─────────────
        $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Spécialité', 'CIN', 'Téléphone', 'Statut', 'Vérifié'];
        foreach ($headers as $i => $label) {
            $sheet->getCell(self::COL[$i] . '1')->setValue($label);
        }
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->setAutoFilter('A1:I1');
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Column widths
        foreach ([8, 20, 20, 35, 30, 15, 16, 12, 14] as $i => $w) {
            $sheet->getColumnDimension(self::COL[$i])->setWidth($w);
        }

        // ── Data rows ─────────────────────────────────────────────────────────
        foreach ($medecins as $i => $medecin) {
            $row = $i + 2;
            $sheet->getCell('A' . $row)->setValue($medecin->getId());
            $sheet->getCell('B' . $row)->setValue($medecin->getLastName());
            $sheet->getCell('C' . $row)->setValue($medecin->getFirstName());
            $sheet->getCell('D' . $row)->setValue($medecin->getEmail());
            $sheet->getCell('E' . $row)->setValue($medecin->getSpecialty());
            $sheet->getCell('F' . $row)->setValue($medecin->getCin());
            $sheet->getCell('G' . $row)->setValue($medecin->getPhoneNumber());
            $sheet->getCell('H' . $row)->setValue($medecin->getIsActive()   ? 'Actif'     : 'Inactif');
            $sheet->getCell('I' . $row)->setValue($medecin->getIsVerified() ? 'Vérifié'   : 'Non vérifié');

            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($dataStyle);
            if ($i % 2 === 1) {
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($altStyle);
            }
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // ── Stream response ───────────────────────────────────────────────────
        $filename = sprintf('medecins_p%d_%s.xlsx', $page, date('Ymd_His'));

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT — PDF
    // ─────────────────────────────────────────────────────────────────────────

#[Route('/export/pdf', name: 'admin_medecin_export_pdf', methods: ['GET'])]
public function exportPdf(
    Request $request,
    MedecinRepository $medecinRepository,
    PaginatorInterface $paginator
): Response {
    $search    = $request->query->get('search',    '');
    $status    = $request->query->get('status',    '');
    $verified  = $request->query->get('verified',  '');
    $specialty = $request->query->get('specialty', '');
    $page      = $request->query->getInt('page', 1);

    $pagination = $paginator->paginate(
        $medecinRepository->createFilteredQueryBuilder($search, $status, $verified, $specialty),
        $page, 15
    );
    $medecins = iterator_to_array($pagination->getItems());

    $html = $this->renderView('admin_medecin/export_pdf.html.twig', [
        'medecins'    => $medecins,
        'page'        => $page,
        'total'       => $pagination->getTotalItemCount(),
        'generatedAt' => new \DateTime(),
        'filters'     => compact('search', 'status', 'verified', 'specialty'),
    ]);

    // mPDF configuration (10mm margins, A4 landscape)
    $mpdf = new Mpdf([
        'mode'              => 'utf-8',
        'format'            => 'A4-L',   // A4 Landscape
        'margin_left'       => 10,
        'margin_right'      => 10,
        'margin_top'        => 10,
        'margin_bottom'     => 10,
        'margin_header'     => 0,
        'margin_footer'     => 0,
    ]);
    $mpdf->WriteHTML($html);
    $pdfContent = $mpdf->Output('', 'S');   // return as string

    $filename = sprintf('medecins_p%d_%s.pdf', $page, date('Ymd_His'));

    return new Response(
        $pdfContent,
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]
    );
}

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD (unchanged)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/new', name: 'admin_medecin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medecin = new Medecin();
        $form    = $this->createForm(MedecinEditType::class, $medecin, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $medecin->setPassword($this->passwordHasher->hashPassword($medecin, $plainPassword));
            } else {
                $this->addFlash('error', 'Un mot de passe est requis pour un nouveau médecin.');
                return $this->render('admin_medecin/new.html.twig', ['medecin' => $medecin, 'form' => $form]);
            }
            $entityManager->persist($medecin);
            $entityManager->flush();
            $this->addFlash('success', 'Médecin créé avec succès!');
            return $this->redirectToRoute('admin_medecin_index');
        }

        return $this->render('admin_medecin/new.html.twig', [
            'medecin' => $medecin,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_medecin_show', methods: ['GET'])]
    public function show(Medecin $medecin): Response
    {
        return $this->render('admin_medecin/show.html.twig', ['medecin' => $medecin]);
    }

    #[Route('/{id}/edit', name: 'admin_medecin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinEditType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $medecin->setPassword($this->passwordHasher->hashPassword($medecin, $plainPassword));
            }
            $entityManager->flush();
            $this->addFlash('success', 'Médecin modifié avec succès!');
            return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
        }

        return $this->render('admin_medecin/edit.html.twig', ['medecin' => $medecin, 'form' => $form]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_medecin_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_status' . $medecin->getId(), $request->request->get('_token'))) {
            $medecin->setIsActive(!$medecin->getIsActive());
            $entityManager->flush();
            $this->addFlash('success', 'Compte médecin ' . ($medecin->getIsActive() ? 'activé' : 'désactivé') . ' avec succès!');
        }
        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }

    #[Route('/{id}/toggle-verification', name: 'admin_medecin_toggle_verification', methods: ['POST'])]
    public function toggleVerification(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_verification' . $medecin->getId(), $request->request->get('_token'))) {
            $medecin->setIsVerified(!$medecin->getIsVerified());
            $entityManager->flush();
            $this->addFlash('success', 'Compte médecin marqué comme ' . ($medecin->getIsVerified() ? 'vérifié' : 'non vérifié') . '!');
        }
        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }

    #[Route('/{id}', name: 'admin_medecin_delete', methods: ['POST'])]
    public function delete(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $medecin->getId(), $request->request->get('_token'))) {
            $entityManager->remove($medecin);
            $entityManager->flush();
            $this->addFlash('success', 'Médecin supprimé avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide!');
        }
        return $this->redirectToRoute('admin_medecin_index');
    }

    #[Route('/{id}/verify-with-official', name: 'admin_medecin_verify_official', methods: ['POST'])]
    public function verifyWithOfficial(
        Medecin $medecin,
        EntityManagerInterface $entityManager,
        DoctorVerificationService $verificationService,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('verify_official' . $medecin->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
        }

        $found = $verificationService->verify($medecin);

        if ($found) {
            $medecin->setIsVerified(true);
            $entityManager->flush();
            $this->addFlash('success', '✅ Médecin trouvé dans l\'annuaire officiel – compte automatiquement vérifié.');
        } else {
            $this->addFlash('warning', '⚠️ Aucune correspondance trouvée dans l\'annuaire officiel.');
        }

        return $this->redirectToRoute('admin_medecin_show', ['id' => $medecin->getId()]);
    }
}
