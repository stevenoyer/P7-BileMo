<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'app_phones', methods: ['GET'])]
    public function getAllPhones(PhoneRepository $phoneRepository): JsonResponse
    {
        $phoneList = $phoneRepository->findAll();

        return $this->json($phoneList, JsonResponse::HTTP_OK);
    }

    #[Route('/api/phones/{id}', name: 'app_detail_phone', methods: ['GET'])]
    public function getDetailPhone(Phone $phone): JsonResponse
    {
        return $this->json($phone, JsonResponse::HTTP_OK);
    }
}
