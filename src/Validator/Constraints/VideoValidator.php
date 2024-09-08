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

use App\Validator\Constraints\Video;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class VideoValidator extends ConstraintValidator
{
    public const KB_BYTES = 1000;
    public const MB_BYTES = 1000000;
    public const KIB_BYTES = 1024;
    public const MIB_BYTES = 1048576;

    private const SUFFICES = [
        1 => 'bytes',
        self::KB_BYTES => 'kB',
        self::MB_BYTES => 'MB',
        self::KIB_BYTES => 'KiB',
        self::MIB_BYTES => 'MiB',
    ];

    /**
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof Video) {
            throw new UnexpectedTypeException($constraint, Video::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if ($value instanceof UploadedFile && !$value->isValid()) {
            switch ($value->getError()) {
                case \UPLOAD_ERR_INI_SIZE:
                    $iniLimitSize = UploadedFile::getMaxFilesize();
                    if ($constraint->maxSize && $constraint->maxSize < $iniLimitSize) {
                        $limitInBytes = $constraint->maxSize;
                        $binaryFormat = $constraint->binaryFormat;
                    } else {
                        $limitInBytes = $iniLimitSize;
                        $binaryFormat = true;
                    }

                    $this->context->buildViolation($constraint->tooLargeMessage)
                        ->setParameter('{{ limit }}', $this->formatSize($limitInBytes, $binaryFormat))
                        ->setParameter('{{ suffix }}', $this->getSuffix($limitInBytes))
                        ->addViolation();

                    return;

                case \UPLOAD_ERR_FORM_SIZE:
                    $this->context->buildViolation($constraint->tooLargeMessage)
                        ->addViolation();

                    return;

                case \UPLOAD_ERR_PARTIAL:
                    $this->context->buildViolation($constraint->notReadableMessage)
                        ->addViolation();

                    return;

                case \UPLOAD_ERR_NO_FILE:
                    $this->context->buildViolation($constraint->notFoundMessage)
                        ->addViolation();

                    return;

                default:
                    return;
            }
        }

        if (!$value instanceof FileObject) {
            throw new UnexpectedValueException($value, 'string or Symfony\\Component\\HttpFoundation\\File\\File');
        }

        // Check file size
        if (null !== $constraint->maxSize && $value->getSize() > $constraint->maxSize) {
            $this->context->buildViolation($constraint->tooLargeMessage)
                ->setParameter('{{ limit }}', $this->formatSize($constraint->maxSize, $constraint->binaryFormat))
                ->setParameter('{{ suffix }}', $this->getSuffix($constraint->maxSize))
                ->addViolation();
        }

        // Directly check mime types without using getMimeTypes()
        if (null !== $constraint->mimeTypes) {
            if (!in_array($value->getMimeType(), $constraint->mimeTypes, true)) {
                $this->context->buildViolation($constraint->mimeTypesMessage)
                    ->setParameter('{{ types }}', implode(', ', $constraint->mimeTypes))
                    ->addViolation();
            }
        }
    }

    private function formatSize(int $size, bool $binaryFormat): string
    {
        if ($binaryFormat) {
            $base = self::KIB_BYTES;
            $suffix = 'iB';
        } else {
            $base = self::KB_BYTES;
            $suffix = 'B';
        }

        $sizeInBase = $size / $base;
        if ($sizeInBase < $base) {
            return round($sizeInBase, 2) . $suffix;
        }

        return round($size / (self::MIB_BYTES), 2) . $suffix;
    }

    private function getSuffix(int $size): string
    {
        foreach (self::SUFFICES as $threshold => $suffix) {
            if ($size < $threshold) {
                return $suffix;
            }
        }

        return 'MiB';
    }
}
