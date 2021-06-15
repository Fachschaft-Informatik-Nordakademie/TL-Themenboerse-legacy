<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController
{
    private ParameterBagInterface $params;
    private Filesystem $filesystem;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->filesystem = new Filesystem();
    }

    #[Route('/file', name: 'file_post', methods: ['post'])]
    public function postFile(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Authentication failed. You have to call this endpoint with a json body either containing email + password or username (ldap) + password'], Response::HTTP_UNAUTHORIZED);
        }
        $fileName = uniqid();
        try {
            $fileContent = $request->getContent(true);
            $directory = $this->params->get('app.file_upload_dir');
            $path = $directory . '/' . $fileName;
            $this->filesystem->mkdir($directory);
            $this->filesystem->dumpFile($path, $fileContent);
        } catch (\Error $e) {
            // TODO: Output error message here
        }

        return $this->json(['id' => $fileName]);
    }

    #[Route('/file/{id}', name: 'file_get', methods: ['get'])]
    public function getFile(string $id): Response
    {
        try {
            $directory = $this->params->get('app.file_upload_dir');
            $path = $directory . '/' . $id;
            return $this->file($path);
        } catch (\Error $e) {
            return $this->json(['message' => 'Error']);
        }
    }
}
