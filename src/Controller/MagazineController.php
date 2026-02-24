<?php

namespace App\Controller;

use App\Entity\Magazine;
use App\Form\MagazineType;
use App\Repository\MagazineRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/magazine')]
final class MagazineController extends AbstractController
{
    #[Route(name: 'app_magazine_index', methods: ['GET'])]
    public function index(MagazineRepository $magazineRepository, EntityManagerInterface $entityManager): Response
    {
        //  CORRECTIF D'URGENCE SQL : Création forcée des colonnes image et pdf_file
        try {
            $conn = $entityManager->getConnection();
            
            // Vérification/Création de 'image'
            $columnsImage = $conn->fetchAllAssociative("SHOW COLUMNS FROM magazine LIKE 'image'");
            if (empty($columnsImage)) {
                $conn->executeStatement("ALTER TABLE magazine ADD image VARCHAR(255) DEFAULT NULL");
            }

            // Vérification/Création de 'pdf_file'
            $columnsPdf = $conn->fetchAllAssociative("SHOW COLUMNS FROM magazine LIKE 'pdf_file'");
            if (empty($columnsPdf)) {
                $conn->executeStatement("ALTER TABLE magazine ADD pdf_file VARCHAR(255) DEFAULT NULL");
            }
        } catch (\Exception $e) {
            // Ignorer en cas d'erreur non critique
        }

        return $this->render('magazine-admin/magazine/index.html.twig', [
            'magazines' => $magazineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_magazine_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $magazine = new Magazine();
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $magazine->setImage($newFilename);
            }

            // Upload PDF
            $pdfFile = $form->get('pdfFileUpload')->getData();
            if ($pdfFile) {
                $fileName = $fileUploader->upload($pdfFile);
                $magazine->setPdfFile($fileName);
            }

            $entityManager->persist($magazine);
            $entityManager->flush();

            return $this->redirectToRoute('app_magazine_index');
        }

        return $this->render('magazine-admin/magazine/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_magazine_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Magazine $magazine,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $form = $this->createForm(MagazineType::class, $magazine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $magazine->setImage($newFilename);
            }

            $pdfFile = $form->get('pdfFileUpload')->getData();
            if ($pdfFile) {
                $fileName = $fileUploader->upload($pdfFile);
                $magazine->setPdfFile($fileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_magazine_index');
        }

        return $this->render('magazine-admin/magazine/edit.html.twig', [
            'form' => $form,
            'magazine' => $magazine,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_show', methods: ['GET'])]
    public function show(Magazine $magazine): Response
    {
        return $this->render('magazine-admin/magazine/show.html.twig', [
            'magazine' => $magazine,
        ]);
    }

    #[Route('/{id}', name: 'app_magazine_delete', methods: ['POST'])]
    public function delete(Request $request, Magazine $magazine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$magazine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($magazine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_magazine_index');
    }
}
