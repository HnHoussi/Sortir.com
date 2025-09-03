<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{

    public function __construct(private SluggerInterface $slugger, private ParameterBagInterface $params)
    {
    }

// FileUploader.php
    public function upload(UploadedFile $file, string $name, string $dir): string
    {
        $filename = $this->slugger->slug($name) . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($dir, $filename);
        return $filename;
    }


}
