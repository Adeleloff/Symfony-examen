<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{id}', name: 'category_item')]
    public function item(Category $category): Response
    {
        return $this->render('category/item.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category', name: 'category_list')]
    public function list(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }
}
