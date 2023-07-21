<?php


namespace Kira0269\LogViewerBundle\LogParser;


interface LogParserInterface
{
    public function __construct(string $logsDir, array $filePattern, string $logPattern, array $groupsConfig);

    public function parseLogs(\DateTime $dateTimeFrom, \DateTime $dateTimeTo, bool $merge = false, string $level = 'ALL'): array;

    public function getErrors(): array;
}
