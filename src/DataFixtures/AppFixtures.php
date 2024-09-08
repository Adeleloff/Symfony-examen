<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Lesson;
use App\Entity\SubCategory;
use App\Entity\Teacher;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Filesystem\Filesystem;

class AppFixtures extends Fixture
{
    private const NB_LESSONS = 10;

    private const NB_TEACHERS = 5;

    private const NB_USERS = 5;

    private const CATEGORIES_NAME = ["Langue Coréenne", "Étiquette Coréenne", "Culture Coréenne", "Les plus"];

    private const SUBCATEGORIES_NAME = ["sous-catégorie 1", "sous-catégorie 2", "sous-catégorie 3", "sous-catégorie 4", "sous-catégorie 5", "sous-catégorie 6", "sous-catégorie 7", "sous-catégorie 8", "sous-catégorie 9", "sous-catégorie 10"];

    // public function __construct() {}


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = [];
        $subcategories = [];
        $teachers = [];
        $users = [];
        $usedUsers = [];
        

        // --- CATEGORIES ------------------------------------------
        foreach (self::CATEGORIES_NAME as $categoryName) {
            $category = new Category();
            $category
                ->setName($categoryName);

            $manager->persist($category);
            $categories[] = $category;
        }

        // --- SOUS-CATEGORIES -------------------------------------
        foreach (self::SUBCATEGORIES_NAME as $subcategoryName) {
            $subcategory = new SubCategory();
            $subcategory
                ->setName($subcategoryName)
                ->setCategory($faker->randomElement($categories));

            $manager->persist($subcategory);
            $subcategories[] = $subcategory;
        }

        // --- USERS -----------------------------------------------
        $admin = new User();
        $admin
            ->setEmail("admin@test.com")
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword("admin1234");

        $manager->persist($admin);

        $user = new User();
        $user
            ->setEmail("user@test.com")
            ->setRoles(["ROLE_TEACHER"])
            ->setPassword("test1234");

        $manager->persist($user);
        $users[] = $user;

        for ($i = 0; $i < self::NB_USERS; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->email())
                ->setRoles(["ROLE_TEACHER"])
                ->setPassword($faker->password());

            $manager->persist($user);
            $users[] = $user;
        }

        // --- PROFESSEURS -----------------------------------------

        for ($i = 0; $i < self::NB_TEACHERS; $i++) {
            // Filtrer les utilisateurs non utilisés
            $availableUsers = array_filter($users, function ($user) use ($usedUsers) {
                return !in_array($user, $usedUsers, true); // Comparaison stricte pour éviter les erreurs
            });

            // Sélectionner un utilisateur disponible
            $user = $faker->randomElement($availableUsers);

            // Marquer l'utilisateur comme utilisé
            $usedUsers[] = $user;

            $teacher = new Teacher();
            $teacher
                ->setLastName($faker->lastName())
                ->setFirstName($faker->firstName())
                ->setDateOfBirth($faker->dateTimeBetween('-40 years', '-23 years'))
                ->setEnrollmentDate($faker->dateTimeBetween('-5 years'))
                ->setDescription($faker->realTextBetween(100, 200))
                ->setProfilePicFilename('imagetest.jpg')
                ->setUser($user); // Utiliser l'utilisateur sélectionné

            $manager->persist($teacher);
            $teachers[] = $teacher;
        }

        // --- LEÇONS ----------------------------------------------

        for ($i = 0; $i < self::NB_LESSONS; $i++) {
            $lesson = new Lesson();
            $lesson
                ->setTitle($faker->realTextBetween(9, 15))
                ->setContent($faker->realTextBetween(350, 700))
                ->setVideoFilename('videotest.mp4')
                ->setCreatedAt($faker->dateTimeBetween('-4 years'))
                ->setVisible($faker->boolean(80))
                ->setSubCategory($faker->randomElement($subcategories))
                ->setTeacher($faker->randomElement($teachers));

            $manager->persist($lesson);
        }

        $manager->flush();
    }
}
