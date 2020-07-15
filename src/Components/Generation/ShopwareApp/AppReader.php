<?php declare(strict_types=1);

namespace FroshPluginUploader\Components\Generation\ShopwareApp;

use FroshPluginUploader\Components\Generation\ShopwarePlatform\ChangelogReader;
use FroshPluginUploader\Components\PluginReaderInterface;

class AppReader implements PluginReaderInterface
{
    private const REQUIRED_KEYS = [
        'label',
        'version',
        'license',
    ];

    private $config;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var ChangelogReader
     */
    private $changelogReader;

    public function __construct(string $rootDir)
    {
        $reader = new ManifestReader();
        $this->config = $reader->read($rootDir . '/manifest.xml');
        $this->rootDir = $rootDir;
    }

    public function validate(): void
    {
        // Validate keys
        foreach (self::REQUIRED_KEYS as $requiredKey) {
            if (!isset($this->config[$requiredKey])) {
                throw new \RuntimeException(sprintf('%s is not defined in manifest.xml', ucfirst($requiredKey)));
            }
        }

        // Call the changelog methods, it will throw a exception if they are missing
        $this->getNewestChangelogGerman();
        $this->getNewestChangelogEnglish();
    }

    public function getVersion(): string
    {
        return $this->config['version'];
    }

    public function getNewestChangelogGerman(): string
    {
        return $this->getChangelogReader()->getChangelog('de-DE', $this->getVersion());
    }

    public function getNewestChangelogEnglish(): string
    {
        return $this->getChangelogReader()->getChangelog('en-GB', $this->getVersion());
    }

    public function getLabelGerman(): string
    {
        return $this->config['label']['de_DE'] ?? $this->config['label']['de'] ?? $this->config['label']['en_GB'] ?? $this->config['label']['en'];
    }

    public function getLabelEnglish(): string
    {
        return $this->config['label']['en_GB'] ?? $this->config['label']['en'];
    }

    public function getDescriptionGerman(): string
    {
        // Missing in manifest.xml
        return '';
    }

    public function getDescriptionEnglish(): string
    {
        // Missing in manifest.xml
        return '';
    }

    public function getLicense(): string
    {
        return $this->config['license'];
    }

    public function getName(): string
    {
        return $this->config['name'];
    }

    private function getChangelogReader(): ChangelogReader
    {
        if ($this->changelogReader === null) {
            $this->changelogReader = new ChangelogReader($this->rootDir);
        }

        return $this->changelogReader;
    }
}
