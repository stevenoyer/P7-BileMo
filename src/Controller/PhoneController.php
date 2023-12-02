<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class PhoneController extends AbstractController
{
    /**
     * Get all phones
     */
    #[Route('/phones', name: 'app_phones', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getAllPhones(PhoneRepository $phoneRepository, Request $request, TagAwareCacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = 'getAllPhones-' . $page . '-' . $limit;

        /* Set cache */
        $jsonPhoneList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit, $serializer) {
            $item->tag('phonesCache');
            $phoneList = $phoneRepository->findAllWithPagination($page, $limit);

            return $serializer->serialize($phoneList, 'json');
        });

        return new JsonResponse($jsonPhoneList, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Get detail phone
     */
    #[Route('/phones/{id}', name: 'app_detail_phone', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($phone, 'json'), JsonResponse::HTTP_OK, [], true);
    }
}
