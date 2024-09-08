<?php

/*
 * Ce fichier a été généré par ChatGPT, avec quelques ajustements de ma part.
 *
 * Je ne voulais pas modifier les fichiers File.php et FileValidator.php
 * car ils sont situés dans le dossier vendor. J'ai donc préféré en générer un du même type, dédié uniquement à la validation des vidéos.
 * 
 * Merci de ta compréhension
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * Class Video
 * @package Symfony\Component\Validator\Constraints
 */
class Video extends Constraint
{
    public const NOT_FOUND_ERROR = 'd2a3fb6e-7ddc-4210-8fbf-2ab345ce1998';
    public const NOT_READABLE_ERROR = 'c20c92a4-5bfa-4202-9477-28e800e0f6ff';
    public const EMPTY_ERROR = '5d743385-9775-4aa5-8ff5-495fb1e60137';
    public const TOO_LARGE_ERROR = 'df8637af-d466-48c6-a59d-e7126250a654';
    public const INVALID_MIME_TYPE_ERROR = '744f00bc-4389-4c74-92de-9a43cde55535';
    public const INVALID_EXTENSION_ERROR = 'c8c7315c-6186-4719-8b71-5659e16bdcb7';
    public const FILENAME_TOO_LONG = 'e5706483-91a8-49d8-9a59-5e81a3c634a8';

    protected const ERROR_NAMES = [
        self::NOT_FOUND_ERROR => 'NOT_FOUND_ERROR',
        self::NOT_READABLE_ERROR => 'NOT_READABLE_ERROR',
        self::EMPTY_ERROR => 'EMPTY_ERROR',
        self::TOO_LARGE_ERROR => 'TOO_LARGE_ERROR',
        self::INVALID_MIME_TYPE_ERROR => 'INVALID_MIME_TYPE_ERROR',
        self::INVALID_EXTENSION_ERROR => 'INVALID_EXTENSION_ERROR',
        self::FILENAME_TOO_LONG => 'FILENAME_TOO_LONG',
    ];

    public $maxSize;
    public $maxSizeMessage = 'The file is too large. Maximum allowed size is {{ limit }} {{ suffix }}.';
    public $mimeTypes = [
        'video/mp4',
        'video/mpeg',
        'video/avi',
        'video/quicktime',
        'video/webm',
        'video/x-msvideo',
        'video/x-flv',
        'video/x-matroska',
    ];

    public $mimeTypesMessage = 'The file is not a valid video format. Allowed formats are {{ types }}.';
    public $notFoundMessage = 'The file could not be found.';
    public $notReadableMessage = 'The file is not readable.';
    public $emptyMessage = 'The file is empty.';
    public $invalidExtensionMessage = 'The file has an invalid extension. Allowed extensions are {{ extensions }}.';
    public $filenameTooLongMessage = 'The file name is too long. Maximum length is {{ limit }} characters.';

    /**
     * Video constructor.
     *
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);

        if (null === $this->maxSize) {
            throw new ConstraintDefinitionException(sprintf('The "maxSize" option must be defined for "%s"', __CLASS__));
        }

        if (!is_array($this->mimeTypes)) {
            throw new ConstraintDefinitionException('The "mimeTypes" option must be an array.');
        }
    }
}
