<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChiffreAffaireController extends AbstractController
{
    /**
     * @Route("backoffice/chiffre-affaire", name="chiffre_affaire")
     */
    public function index(): Response
    {
        return $this->render('admin/chiffre_affaire/index.html.twig');
    }
}
