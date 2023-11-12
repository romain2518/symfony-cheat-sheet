<?php
// src/Service/FileUploader.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileUploader
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    /**
     * To use, write this in a controller
     * 
     * ? Param to add : 
     * FileUploader $fileUploader
     * 
     * ? Code to add :
     * $file = $request->files->get('fileupload');
     * $filename = $fileUploader->upload($file, 'user');
     * if ($filename['isFailed']) {
     *     return $this->json($filename['error'], $filename['responseCode']);
     * }
     */

    /**
     * Uploads a given file
     * 
     * @param UploadedFile $file
     * @return string|false
     */
    public function upload(UploadedFile $file)
    {
        // Validating file
        $errorList = $this->validateImage($file);
        if (count($errorList) > 0) {
            return [
                'isFailed' => true,
                'error' => 'Error: Something bad happened during file upload.',
                'responseCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // Moving uploaded file
        $filename = uniqid().'.'.$file->guessExtension();
        $targetDirectory = '../public/images/my_directory';

        try {
            $file->move($targetDirectory, $filename);
        } catch (FileException $e) {
            return [
                'isFailed' => true,
                'error' => $e,
                'responseCode' => Response::HTTP_UNPROCESSABLE_ENTITY
            ];
        }

        // Returning filename on success
        return [
            'isFailed' => false,
            'filename' => $filename
        ];
    }

    public function validateImage(UploadedFile $file)
    {
        // Setting up constraints
        $fileContraints = new Image();
        $fileContraints->maxSize   = '5M';
        $fileContraints->mimeTypes = [
            "image/png",
            "image/jpeg",
            "image/jpg"
        ];

        // Validating the image file
        $errorList = $this->validator->validate(
            $file,
            $fileContraints
        );

        return $errorList;
    }
}