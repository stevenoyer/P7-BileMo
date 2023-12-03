<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerService
{

    private $jwt;
    private $customerRepository;

    public function __construct(JWTEncoderInterface $jwt, CustomerRepository $customerRepository)
    {
        $this->jwt = $jwt;
        $this->customerRepository = $customerRepository;
    }

    public function getCustomerByToken(Request $request): Customer|null
    {
        $token = substr($request->headers->get('Authorization'), 7);
        $username = $this->jwt->decode($token)['username'];

        return $this->customerRepository->findOneBy(['email' => $username]);
    }

    public function isGranted(Request $request, Phone|User|Customer $entity): bool
    {
        $customer = $this->getCustomerByToken($request);

        if ($customer != $entity->getCustomer()) {
            return false;
        }

        return true;
    }
}
