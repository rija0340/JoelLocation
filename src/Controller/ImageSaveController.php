<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageSaveController extends AbstractController
{
    /**
     * @Route("/image/save", name="image_save", methods={"GET","POST"})
     */
    public function index(Request $request): Response
    {
        $file = $request->files->get('image');
        $fileName = $file->getClientOriginalName();
        $file->move(
            $this->getParameter('vehicules_directory'),
            $fileName
        );
        /* return $this->render('image_save/index.html.twig', [
            'controller_name' => 'ImageSaveController',
        ]); */
        return new JsonResponse(['filesnames' => $fileName]);
    }
}
