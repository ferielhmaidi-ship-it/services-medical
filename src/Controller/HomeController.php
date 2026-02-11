<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MedecinRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Medecin;
use App\Repository\PatientRepository;
use App\Constants\Specialty;
use App\Constants\Governorate;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/doctors', name: 'app_doctors')]
    public function doctors(Request $request, MedecinRepository $medecinRepository): Response
    {
        $name = $request->query->get('name', '');
        $specialty = $request->query->get('specialty', '');
        $governorate = $request->query->get('governorate', '');

        // Use repository method for filtering
        $medecins = $medecinRepository->searchDoctors($name, $specialty, $governorate);

        // Get filter options
        $filterOptions = $medecinRepository->getFilterOptions();

        return $this->render('pages/doctors.html.twig', [
            'medecins' => $medecins,
            'name' => $name,
            'currentSpecialty' => $specialty, // Renamed to avoid conflict with loop variable
            'currentGovernorate' => $governorate, // Renamed to avoid conflict

            // Serve filters directly from Constants
            'governorates' => Governorate::getChoices(),
            // We pass groups for a better dropdown experience
            'specialtyGroups' => Specialty::getGroups(),
            // We also pass the static class name to use helper methods in Twig if needed
            'specialtyClass' => Specialty::class
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/appointment', name: 'app_appointment')]
    public function appointment(): Response
    {
        return $this->render('pages/appointment.html.twig');
    }

    #[Route('/departments', name: 'app_departments')]
    public function departments(): Response
    {
        return $this->render('pages/departments.html.twig');
    }

    #[Route('/department-details', name: 'app_department_details')]
    public function departmentDetails(): Response
    {
        return $this->render('pages/department-details.html.twig');
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig');
    }

    #[Route('/service-details', name: 'app_service_details')]
    public function serviceDetails(): Response
    {
        return $this->render('pages/service-details.html.twig');
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function gallery(): Response
    {
        return $this->render('pages/gallery.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('pages/faq.html.twig');
    }

    #[Route('/testimonials', name: 'app_testimonials')]
    public function testimonials(): Response
    {
        return $this->render('pages/testimonials.html.twig');
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/privacy.html.twig');
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('pages/terms.html.twig');
    }
}
