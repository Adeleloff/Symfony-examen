<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubCategoryController extends AbstractController
{
    #[Route('/subcategory/{id}', name: 'subcategory_item')]
    public function item(SubCategory $subcategory): Response
    {
        return $this->render('sub_category/index.html.twig', [
            'subcategory' => $subcategory,
        ]);
    }
}
