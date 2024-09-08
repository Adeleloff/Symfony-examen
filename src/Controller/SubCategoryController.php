<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subcategory')]
class SubCategoryController extends AbstractController
{
    #[Route('/new', name: 'subcategory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subCategory);
            $entityManager->flush();

            return $this->redirectToRoute('home_page', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sub_category/new.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }


    #[Route('/edit/{id}', name: 'subcategory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('home_page', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sub_category/edit.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'subcategory_item', methods: ['GET'])]
    public function item(SubCategory $subcategory): Response
    {
        return $this->render('sub_category/item.html.twig', [
            'subcategory' => $subcategory,
        ]);
    }

    #[Route('/{id}', name: 'subcategory_delete', methods: ['POST'])]
    public function delete(Request $request, SubCategory $subCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home_page', [], Response::HTTP_SEE_OTHER);
    }
}
