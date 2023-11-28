<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class PhoneController extends AbstractController
{
    #[Route('/phones', name: 'app_phones', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getAllPhones(PhoneRepository $phoneRepository, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = 'getAllPhones-' . $page . '-' . $limit;

        /* Set cache */
        $phoneList = $cache->get($idCache, function (ItemInterface $item) use ($phoneRepository, $page, $limit) {
            $item->tag('phonesCache');
            return $phoneRepository->findAllWithPagination($page, $limit);
        });

        return $this->json($phoneList, JsonResponse::HTTP_OK);
    }

    #[Route('/phones/{id}', name: 'app_detail_phone', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getDetailPhone(Phone $phone): JsonResponse
    {
        return $this->json($phone, JsonResponse::HTTP_OK);
    }
}
