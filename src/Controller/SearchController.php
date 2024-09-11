<?php
namespace App\Controller;

use App\Repository\LessonRepository;
use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(Request $request, LessonRepository $lessonRepository, TeacherRepository $teacherRepository): Response
    {
        $query = $request->query->get('q');

        if (!$query) {
            return $this->redirectToRoute('home_page');
        }

        $lessons = $lessonRepository->findBySearchQuery($query);

        return $this->render('search/results.html.twig', [
            'lessons' => $lessons,
            'query' => $query,
        ]);
    }
}
