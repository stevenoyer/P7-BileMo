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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api')]
class PhoneController extends AbstractController
{
    /**
     * Get all phones
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des téléphones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Response(
     *     response = 401,
     *     description = "Vous n'êtes pas authentifié."
     * )
     * 
     * @OA\Response(
     *     response = 404,
     *     description = "Cette ressource n'existe pas."
     * )
     * 
     * @OA\Tag(name="Phone")
     * 
     * @param PhoneRepository $phoneRepository
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @param SerializerInterface $serializer
     * 
     * @return JsonResponse
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
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un téléphone",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     * 
     * @OA\Parameter(
     *    name="id",
     *    in="path",
     *    description="Id du téléphone",
     *    required=true,
     *    @OA\Schema(
     *        type="integer"
     *    )
     *  )
     * 
     * @OA\Response(
     *     response = 401,
     *     description = "Vous n'êtes pas authentifié."
     * )
     * 
     * @OA\Response(
     *     response = 404,
     *     description = "Cette ressource n'existe pas."
     * )
     * 
     * @OA\Tag(name="Phone")
     * 
     * @param Phone $phone
     * @param SerializerInterface $serializer
     * 
     * @return JsonResponse
     */
    #[Route('/phones/{id}', name: 'app_detail_phone', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse($serializer->serialize($phone, 'json'), JsonResponse::HTTP_OK, [], true);
    }
}
