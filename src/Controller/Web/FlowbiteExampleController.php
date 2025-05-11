<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Test controller demonstrating Flowbite UI components integration.
 * 
 * This controller serves as an example of how to use Flowbite components
 * in Twig templates. It showcases various UI elements like buttons, cards,
 * and form inputs with proper Tailwind CSS classes and dark mode support.
 * 
 * @note This is for testing and demonstration purposes only.
 */
class FlowbiteExampleController extends AbstractController
{
    #[Route('/flowbite/example', name: 'app_flowbite_example')]
    public function index(): Response
    {
        return $this->render('flowbite/example.html.twig', [
            'title' => 'Flowbite Components Example',
        ]);
    }
} 