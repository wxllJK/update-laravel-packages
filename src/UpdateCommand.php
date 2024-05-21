<?php

namespace Wxlljk\UpdateLaravelPackages;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommand extends Command
{
    protected static $defaultName = 'update';

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
        ->setDescription('Update Laravel framework version based on predefined rules')
        ->addArgument('newMajorVersion', InputArgument::REQUIRED, 'The new major version to update to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $composerJsonPath = getcwd() . '/composer.json';
        if (!file_exists($composerJsonPath)) {
            $io->error('composer.json file not found.');
            return Command::FAILURE;
        }

        $composerJsonContent = file_get_contents($composerJsonPath);
        $composerData = json_decode($composerJsonContent, true);
        $currentMajorVersion = explode('.', str_replace('^', '', $composerData['require']['laravel/framework']))[0];
        $newMajorVersion = $input->getArgument('newMajorVersion');

        if ($newMajorVersion <= $currentMajorVersion) {
            $io->error('New version must be higher than current version');
            return Command::FAILURE;
        }

        $sets = $this->getUpdateSets($currentMajorVersion, $newMajorVersion);
        if (empty($sets)) {
            $io->error('No update sets found');
            return Command::FAILURE;
        }

        $updatedPackages = [];
        foreach ($sets['rules'] as $package => $version) {
            if (array_key_exists($package, $composerData['require'])) {
                $composerData['require'][$package] = $version;
                $updatedPackages[] = $package . ': ' . $version;
            } elseif (array_key_exists($package, $composerData['require-dev'])) {
                $composerData['require-dev'][$package] = $version;
                $updatedPackages[] = $package . ': ' . $version;
            }
        }

        file_put_contents($composerJsonPath, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $io->info('composer.json has been updated with the following changes:');
        $io->listing($updatedPackages);

        if (count($sets['replacements'])) {
            $io->info('You should replace the following packages:');
            $io->listing(array_map(function($package, $newPackage) {
                return '"' . $package . '" -> "' . $newPackage['package'] . '": "' . $newPackage['version'] . '"';
            }, array_keys($sets['replacements']), $sets['replacements']));
        }

        return Command::SUCCESS;
    }

    private function getUpdateSets($oldVersion, $newVersion): array
    {
        $rules = [];
        $replacements = [];
        for ($i = $oldVersion + 1; $i <= $newVersion; $i++) {
            $rules = array_merge($rules, $this->getUpdatesForVersion($i));
            $replacements = array_merge($replacements, $this->getReplacementsForVersion($i));
        }

        return [
            'rules' => $rules,
            'replacements' => $replacements,
        ];
    }

    private function getReplacementsForVersion(int $newVersion): array
    {
        $replacements = [
            '9' => [
                'facade/ignition' => [
                    'package' => 'spatie/laravel-ignition',
                    'version' => '^1.0',
                ],
            ],
        ];

        return $replacements[$newVersion] ?? [];
    }

    private function getUpdatesForVersion(int $newVersion): array
    {
        $rules = [
            '11' => [
                'laravel/framework' => '^11.0',
                'nunomaduro/collision' => '^8.1',
                'laravel/breeze' => '^2.0',
                'laravel/cashier' => '^15.0',
                'laravel/dusk' => '^8.0',
                'laravel/jetstream' => '^5.0',
                'laravel/octane' => '^2.3',
                'laravel/passport' => '^12.0',
                'laravel/sanctum' => '^4.0',
                'laravel/spark-stripe' => '^5.0',
                'laravel/telescope' => '^5.0',
                'inertiajs/inertia-laravel' => '^1.0'
            ],
            '10' => [
                'laravel/framework' => '^10.0',
                'laravel/sanctum' => '^3.2',
                'doctrine/dbal' => '^3.0',
                'spatie/laravel-ignition' => '^2.0',
                'laravel/passport' => '^11.0',
                'laravel/ui' => '^4.0',
            ],
            '9' => [
                'laravel/framework' => '^9.0',
                'nunomaduro/collision' => '^6.1',
                'pusher/pusher-php-server' => '^5.0'
            ],
            '8' => [
                'guzzlehttp/guzzle' => '^7.0.1',
                'facade/ignition' => '^2.3.6',
                'laravel/framework' => '^8.0',
                'laravel/ui' => '^3.0',
                'nunomaduro/collision' => '^5.0',
                'phpunit/phpunit' => '^9.0',
            ],
        ];

        return $rules[$newVersion] ?? [];
    }
}
