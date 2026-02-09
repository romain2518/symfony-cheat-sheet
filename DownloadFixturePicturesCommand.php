<?php
# src/Command
namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:download-fixture-pictures',
    description: 'Download pictures from copyright free websites.',
)]
class DownloadFixturePicturesCommand extends Command
{
    public function __invoke(
        SymfonyStyle $io,
        OutputInterface $output,
        #[Argument(description: 'How many images will be downloaded')] int $howMany = 20,
        #[Argument] string $folderName = 'userPictures',
        )

    {
        $dirPath = "public/assets/images/$folderName";
        $defaultPictureNames = [
            '0.svg',
            '0.jpg',
            '0.jpeg',
            '0.png',
            '0.jfif',
        ];

        //! Create directory if it doesn't exist
        if (!is_dir($dirPath)) {
            mkdir($dirPath, recursive: true);
        }

        //! Removing current pictures except default one
        $io->section('Removing current pictures : in progress');

        $finder = new Finder();
        $fileSystem = new Filesystem();

        $files = $finder->files()->notName($defaultPictureNames)->in($dirPath);
        $fileSystem->remove($files);

        $io->info('Removing current pictures : OK');

        //! Downloading new pictures
        //? https://i.pravatar.cc/200?img={number} will return an image

        $io->section('Downloading pictures : in progress');

        $progressBar = new ProgressBar($output, $howMany);

        for ($i=1; $i < $howMany+1; $i++) { 
            $content = file_get_contents("https://i.pravatar.cc/200?img=" . $i);

            // Store in the file system
            $filepointer = fopen("$dirPath/$i.jfif", 'w');
            fwrite($filepointer, $content);
            fclose($filepointer);

            $progressBar->advance();
        }

        $io->info('Downloading pictures : OK');


        $io->success("$howMany pictures successfully downloaled");

        return Command::SUCCESS;
    }
}
