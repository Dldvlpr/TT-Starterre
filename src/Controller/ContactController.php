<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use App\DTO\PersonContactDTO;
use App\DTO\CompanyContactDTO;
use App\Service\CSVService;

/**
 * Contrôleur pour la gestion du formulaire de contact.
 */
final class ContactController extends AbstractController
{
    /**
     * Affiche le formulaire de contact.
     */
    #[Route('/contact', name: 'contact_form', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'insee_api_key' => $_ENV['INSEE_API_KEY'] ?? ''
        ]);
    }

    /**
     * Soumission du formulaire de contact.
     * @param CSVService $csvService Service de sauvegarde CSV
     */
    #[Route('/contact/submit', name: 'contact_submit', methods: ['POST'])]
    public function submitContact(
        Request $request,
        ValidatorInterface $validator,
        CsrfTokenManagerInterface $csrfTokenManager,
        CSVService $csvService,
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json(['error' => 'Données JSON invalides'], 400);
            }

            $token = new CsrfToken('contact_form', $data['_token'] ?? '');
            if (!$csrfTokenManager->isTokenValid($token)) {
                return $this->json(['error' => 'Token CSRF invalide'], 403);
            }

            $isCompany = ($data['contactType'] ?? '') === 'company';

            $dto = $isCompany
                ? CompanyContactDTO::fromArray($data)
                : PersonContactDTO::fromArray($data);

            $errors = $validator->validate($dto);

            if (count($errors) > 0) {
                return $this->json([
                    'errors' => $this->formatErrors($errors)
                ], 400);
            }

            $csvService->save($dto, $isCompany);

            return $this->json([
                'message' => 'Contact enregistré avec succès',
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formate les erreurs de validation pour la réponse JSON.
     */
    private function formatErrors($errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formattedErrors;
    }
}
