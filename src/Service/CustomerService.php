<?php

namespace App\Service;

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

    public function getCustomerByToken(Request $request)
    {
        $token = substr($request->headers->get('Authorization'), 7);
        $username = $this->jwt->decode($token)['username'];

        return $this->customerRepository->findOneBy(['email' => $username]);
    }

    public function isGranted(Request $request, $entity)
    {
        $customer = $this->getCustomerByToken($request);

        if ($customer != $entity->getCustomer()) {
            return false;
        }

        return true;
    }
}
