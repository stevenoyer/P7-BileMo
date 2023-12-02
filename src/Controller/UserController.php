<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\ForbiddenException;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Service\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    private $em;
    private $serializer;
    private $urlGenerator;
    private $customerRepository;
    private $userRepository;
    private $userPasswordHasher;
    private $customerService;
    private $validator;

    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        CustomerService $customerService,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->customerService = $customerService;
        $this->validator = $validator;
    }

    /**
     * Get all uers
     */
    #[Route('/users', name: 'app_users', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getAllUsers(Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        /* Pagination */
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        /* Get customer by Token Authentication */
        $customer = $this->customerService->getCustomerByToken($request);
        $idCache = 'getAllUsers-' . $page . '-' . $limit;

        /* Set cache */
        $jsonUsersList = $cache->get($idCache, function (ItemInterface $item) use ($page, $limit, $customer) {
            $item->tag('getAllUsers');
            $usersList = $this->userRepository->findAllWithPagination($page, $limit, $customer);

            $context = SerializationContext::create()->setGroups(['getUsers']);
            return $this->serializer->serialize($usersList, 'json', $context);
        });

        return new JsonResponse($jsonUsersList, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * Get user by id
     */
    #[Route('/users/{id}', name: 'app_detail_user', methods: ['GET'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function getDetailUser(User $user, Request $request): JsonResponse
    {
        if (!$this->customerService->isGranted($request, $user)) {
            throw new ForbiddenException('Vous n\'avez pas accès à cette ressource.');
        }

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, JsonResponse::HTTP_FOUND, [], true);
    }

    /**
     * Create user
     */
    #[Route('/users', name: 'app_create_user', methods: ['POST'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function createUser(Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        /* Get content in table form */
        $content = $request->toArray();
        $customerId = $content['customer_id'] ?? -1;

        /* Set find customer by customer_id in content */
        $user->setCustomer($this->customerRepository->find($customerId));
        /* Define password hash by password in content */
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $content['password']));

        /* Check for errors */
        $errors = $this->validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $cache->invalidateTags(['getAllUsers']);

        $this->em->persist($user);
        $this->em->flush();

        /* Generate the url for the http header */
        $location = $this->urlGenerator->generate('app_detail_user', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, JsonResponse::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Delete user by id
     */
    #[Route('/users/{id}', name: 'app_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_CUSTOMER', message: 'Vous devez être authentifié en tant que Client pour accéder à cette ressource.')]
    public function deleteUser(User $user, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        if (!$this->customerService->isGranted($request, $user)) {
            throw new ForbiddenException('Vous n\'avez pas accès à cette ressource.');
        }

        $cache->invalidateTags(['getAllUsers']);
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
