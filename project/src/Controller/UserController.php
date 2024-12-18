<?php

namespace App\Controller;

use App\DTO\IdRequestDTO;
use App\DTO\PostRequestDTO;
use App\DTO\PutRequestDTO;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/v1/api/users', name: 'api_users')]
class UserController extends AbstractController
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $response = ['login', 'phone', 'pass'];

        if (!$this->checkAccess($request->getMethod(), $currentUser)) {
            return $this->json($response, Response::HTTP_FORBIDDEN);
        }

        $checkRequest = $this->handleRequest($request);

        if ($checkRequest) {
            $response['error'] = $checkRequest['error'];
            return $this->json($response, $checkRequest['status']);
        }

        $id = $request->get('id');

        if (in_array(UserRole::ROLE_USER, $currentUser->getRoles())) {
            if($currentUser->getId() != $id) {
                return $this->json($response, Response::HTTP_FORBIDDEN);
            }
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!empty($user)) {
            $response = [
                'login' => $user->getLogin(),
                'phone' => $user->getPhone(),
                'pass' => $user->getPass()
            ];
        }else{
            return $this->json($response, Response::HTTP_NOT_FOUND);
        }
        return $this->json($response);
    }

    /** Зазвичай PUT використовують для оновлення даних, але у тз судячи по обов'язкових атрібутах потрібно створення */
    #[Route('', methods: ['PUT'])]
    public function create(
        Request                $request,
        EntityManagerInterface $em,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$this->checkAccess($request->getMethod(), $currentUser)) {
            return $this->json(['id' => ''], Response::HTTP_FORBIDDEN);
        }

        $checkRequest = $this->handleRequest($request);

        if ($checkRequest) {
            return $this->json(['id' => '', 'error' => $checkRequest['error']], $checkRequest['status']);
        }

        $data = json_decode($request->getContent(), true);

        if (in_array(UserRole::ROLE_USER, $currentUser->getRoles())) {
            if($data['login'] !== $currentUser->getLogin()){
                return $this->json(['id'  => ''], Response::HTTP_FORBIDDEN);
            }
        }

        $user = (new User())
            ->setLogin($data['login'])
            ->setPhone($data['phone'])
            ->setPass($data['pass']);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(['id'  => '', 'error' =>  (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);

        $role = new UserRole();
        $role->setUser($user);
        // Усі нові користувачі будуть мати роль ROLE_USER
        $role->setName(UserRole::ROLE_USER);
        $em->persist($role);
        $em->flush();


        return $this->json(['id' => $user->getId()], Response::HTTP_CREATED);
    }

    /** Зазвичай POST використовують для створення даних, але у тз судячи по обов'язкових атрібутах потрібно оновлення */
    #[Route('', methods: ['POST'])]
    public function update(
        Request                $request,
        EntityManagerInterface $em,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$this->checkAccess($request->getMethod(), $currentUser)) {
            return $this->json(['Not allowed'], Response::HTTP_FORBIDDEN);
        }

        $checkRequest = $this->handleRequest($request);
        $response = ['id' => '', 'login' => '', 'phone' => '', 'pass' => ''];
        if ($checkRequest) {
            $response['error'] = $checkRequest['error'];
            return $this->json($response, $checkRequest['status']);
        }

        $data = json_decode($request->getContent(), true);
        // можна передати id через роут і одразу отримати користувача але по тз потрібен параметр id
        $user = $em->getRepository(User::class)->find($data['id'] ?? null);
        if(empty($user)) {
            $response['error'] = 'not found';
            return $this->json($response, Response::HTTP_NOT_FOUND);
        }

        if (in_array(UserRole::ROLE_USER, $currentUser->getRoles()) && $user->getLogin() !== $currentUser->getUserIdentifier()) {
            $response['error'] = 'not alloved';
            return $this->json($response, Response::HTTP_FORBIDDEN);
        }

        $user->setLogin($data['login']);
        $user->setPhone($data['phone']);
        $user->setPass($data['pass']);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $response['error'] = (string)$errors;
            return $this->json($response, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'phone' => $user->getPhone(),
            'pass' => $user->getPass()
        ], Response::HTTP_OK);
    }

    #[Route('', methods: ['DELETE'])]
    public function delete(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$this->checkAccess($request->getMethod(), $currentUser)) {
            return $this->json(['Not allowed'], Response::HTTP_FORBIDDEN);
        }

        $checkRequest = $this->handleRequest($request);

        if ($checkRequest) {
            return $this->json(['error' => $checkRequest['error']], $checkRequest['status']);
        }
        // можна передати id через роут і одразу отримати користувача але по тз потрібен параметр id
        $data = json_decode($request->getContent(), true);
        $user = $em->getRepository(User::class)->find($data['id'] ?? null);
        if(empty($user)) {
            $response['error'] = 'not found';
            return $this->json($response, Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([], Response::HTTP_OK);
    }

    private function handleRequest(Request $request): array|false
    {
        $method = $request->getMethod();

        switch ($method) {
            case 'PUT':
                $dto = $this->serializer->deserialize($request->getContent(), PutRequestDTO::class, 'json');
                break;

            case 'POST':
                $dto = $this->serializer->deserialize($request->getContent(), PostRequestDTO::class, 'json');
                break;

            case 'GET':
                $dto = $this->serializer->deserialize(json_encode($request->query->all()), IdRequestDTO::class, 'json');
                break;

            case 'DELETE':
                $dto = $this->serializer->deserialize($request->getContent(), IdRequestDTO::class, 'json');
                break;

            default:
                return ['error' => 'Unsupported method', 'status' => Response::HTTP_METHOD_NOT_ALLOWED];
        }

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return ['error' => implode(' ', $errorMessages), 'status' => Response::HTTP_BAD_REQUEST];
        }

        return false;
    }

    private function checkAccess($method, $user): bool
    {
        if (empty($method) || empty($user)) {
            return false;
        }

        $role = $user->getRoles()[0] ?? false;

        if (!$role) {
            return false;
        }

        if ($role == UserRole::ROLE_ADMIN && in_array($method, UserRole::ALLOWED_METHODS_ADMIN)) {
            return true;
        }

        if ($role == UserRole::ROLE_USER && in_array($method, UserRole::ALLOWED_METHODS_USER)) {
            return true;
        }

        return false;
    }
}
