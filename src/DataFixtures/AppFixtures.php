<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Lesson;
use App\Entity\SubCategory;
use App\Entity\Teacher;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        // --- CATEGORIES ------------------------------------------
        $categoryLangue = new Category();
        $categoryLangue->setName("Langue Coréenne");
        $manager->persist($categoryLangue);

        $categoryCulture = new Category();
        $categoryCulture->setName("Culture Coréenne");
        $manager->persist($categoryCulture);

        $categoryHistoire = new Category();
        $categoryHistoire->setName("Histoire Coréenne");
        $manager->persist($categoryHistoire);

        $categoryTraditions = new Category();
        $categoryTraditions->setName("Traditions et Coutumes");
        $manager->persist($categoryTraditions);

        // --- SOUS-CATEGORIES -------------------------------------
        $subcategories = [];

        $subcategories[] = (new SubCategory())
            ->setName("Introduction au Coréen")
            ->setCategory($categoryLangue);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Grammaire et Vocabulaire")
            ->setCategory($categoryLangue);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Culture Populaire Coréenne")
            ->setCategory($categoryCulture);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Cinéma et Séries Coréennes")
            ->setCategory($categoryCulture);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Histoire Moderne de la Corée")
            ->setCategory($categoryHistoire);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Coutumes et Traditions Familiales")
            ->setCategory($categoryTraditions);
        $manager->persist(end($subcategories));

        $subcategories[] = (new SubCategory())
            ->setName("Cuisine Coréenne")
            ->setCategory($categoryCulture);
        $manager->persist(end($subcategories));

        // --- UTILISATEURS ----------------------------------------
        $admin = new User();
        $admin->setEmail("admin@koreancourses.com")
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword("adminpassword");
        $manager->persist($admin);

        $teacher1 = new User();
        $teacher1->setEmail("kim@koreancourses.com")
            ->setRoles(["ROLE_TEACHER"])
            ->setPassword("teacherpassword1");
        $manager->persist($teacher1);

        $teacher2 = new User();
        $teacher2->setEmail("lee@koreancourses.com")
            ->setRoles(["ROLE_TEACHER"])
            ->setPassword("teacherpassword2");
        $manager->persist($teacher2);

        $teacher3 = new User();
        $teacher3->setEmail("park@koreancourses.com")
            ->setRoles(["ROLE_TEACHER"])
            ->setPassword("teacherpassword3");
        $manager->persist($teacher3);

        // --- PROFESSEURS -----------------------------------------
        $teachers = [];

        $teacherEntity1 = new Teacher();
        $teacherEntity1->setLastName("Kim")
            ->setFirstName("Ji-woo")
            ->setDateOfBirth(new \DateTime('1985-08-12'))
            ->setEnrollmentDate(new \DateTime('2018-03-01'))
            ->setDescription("Professeur de langue coréenne avec 10 ans d'expérience dans l'enseignement.")
            ->setProfilePicFilename('imagetest.jpg')
            ->setUser($teacher1);
        $manager->persist($teacherEntity1);
        $teachers[] = $teacherEntity1;

        $teacherEntity2 = new Teacher();
        $teacherEntity2->setLastName("Lee")
            ->setFirstName("Min-ho")
            ->setDateOfBirth(new \DateTime('1987-06-23'))
            ->setEnrollmentDate(new \DateTime('2019-04-15'))
            ->setDescription("Spécialiste de la culture populaire et du cinéma coréen.")
            ->setProfilePicFilename('imagetest.jpg')
            ->setUser($teacher2);
        $manager->persist($teacherEntity2);
        $teachers[] = $teacherEntity2;

        $teacherEntity3 = new Teacher();
        $teacherEntity3->setLastName("Park")
            ->setFirstName("Su-bin")
            ->setDateOfBirth(new \DateTime('1990-10-07'))
            ->setEnrollmentDate(new \DateTime('2020-02-20'))
            ->setDescription("Historien passionné de l’histoire moderne et des coutumes coréennes.")
            ->setProfilePicFilename('imagetest.jpg')
            ->setUser($teacher3);
        $manager->persist($teacherEntity3);
        $teachers[] = $teacherEntity3;

        // --- LEÇONS ----------------------------------------------
        $lesson1 = new Lesson();
        $lesson1->setTitle("Les Bases de la Langue Coréenne")
            ->setContent("Ce cours couvre les bases du coréen, y compris l'alphabet Hangul et les salutations de base.")
            ->setVideoFilename('videotest.mp4')
            ->setCreatedAt(new \DateTime('2021-05-12'))
            ->setVisible(true)
            ->setSubCategory($subcategories[0])
            ->setTeacher($teachers[0]);
        $manager->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle("Grammaire Coréenne Avancée")
            ->setContent("Dans ce cours, nous allons approfondir la grammaire coréenne, notamment les conjugaisons complexes.")
            ->setVideoFilename('videotest.mp4')
            ->setCreatedAt(new \DateTime('2022-06-20'))
            ->setVisible(true)
            ->setSubCategory($subcategories[1])
            ->setTeacher($teachers[0]);
        $manager->persist($lesson2);

        $lesson3 = new Lesson();
        $lesson3->setTitle("Histoire Moderne de la Corée")
            ->setContent("Découvrez l’histoire moderne de la Corée, de l’occupation japonaise à l’ère contemporaine.")
            ->setVideoFilename('videotest.mp4')
            ->setCreatedAt(new \DateTime('2021-08-18'))
            ->setVisible(true)
            ->setSubCategory($subcategories[4])
            ->setTeacher($teachers[2]);
        $manager->persist($lesson3);

        $lesson4 = new Lesson();
        $lesson4->setTitle("Cinéma Coréen Contemporain")
            ->setContent("Ce cours explore l’industrie cinématographique coréenne et ses productions récentes.")
            ->setVideoFilename('videotest.mp4')
            ->setCreatedAt(new \DateTime('2022-10-11'))
            ->setVisible(true)
            ->setSubCategory($subcategories[3])
            ->setTeacher($teachers[1]);
        $manager->persist($lesson4);

        // Sauvegarder toutes les entités
        $manager->flush();
    }
}
