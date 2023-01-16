<?php


namespace Kira0269\LogViewerBundle\LogParser;


use DateTime;
use Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\PathFilterIterator;
use Symfony\Component\Finder\SplFileInfo;

class LogParser implements LogParserInterface
{
    const ALL_FILES = 'all';

    private string $logsDir;
    private array $filePattern;
    private string $logPattern;
    private array $groupsConfig;

    private array $errors = [];

    /**
     * LogParser constructor.
     *
     * @param string    $logsDir        - Logs directory.
     * @param array     $filePattern    - Log filenames pattern.
     * @param string    $logPattern     - Log pattern.
     * @param array     $groupsConfig   - Parsing rules defined in config.
     */
    public function __construct(string $logsDir, array $filePattern, string $logPattern, array $groupsConfig)
    {
        $this->logsDir = $logsDir;
        $this->filePattern = $filePattern;
        $this->logPattern = $logPattern;
        $this->groupsConfig = $groupsConfig;
    }

    /**
     * Return errors thrown during parsing.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Return true if the log parser has errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Return all parsed files.
     *
     * @param string|null $pattern
     *
     * @return PathFilterIterator
     * @throws Exception
     */
    public function getFiles(string $pattern = null): PathFilterIterator
    {
        $finder = new Finder();
        $finderPattern = $pattern ?: "*";
        return $finder
            ->in($this->logsDir)
            ->files()
            ->name($finderPattern)
            ->getIterator();
    }

    /**
     * Return all possible files dates.
     *
     * @return array
     * @throws Exception
     */
    public function getFilesDates(): array
    {
        $dates = [];
        $dates[] = new \DateTime();

        $finder = new Finder();
        $files = $finder
            ->in($this->logsDir)
            ->files()
            ->name('*')
            ->getIterator();

        foreach ($files as $fileInfo) {
            $date = [];
            $success = preg_match('/\-([0-9\-]+).log*/', $fileInfo->getFilename(), $date);
            if ($success) {
                try {
                    if ($fileDate = \DateTime::createFromFormat($this->filePattern['date_format'], $date[1])) {
                        if (!in_array($fileDate, $dates)) {
                            $dates[] = $fileDate;
                        }
                    }
                } catch (\ErrorException $e) {
                    // cannot get date from filename with regexp
                    $dates[] = $fileInfo->getFilename();
                }
            } else {
                // regexp did not match
                $dates[] = $fileInfo->getFilename();
            }
        }

        rsort($dates);
        return $dates;
    }

    /**
     * Parse all .log files for a date in logs directory
     * and return them in an array.
     *
     * @param DateTime $dateTime
     *
     * @param bool     $merge - If true, merge all logs from several files into one array.
     * @param string   $filePattern - If not null, filter on log file name pattern
     *
     * @return array
     * @throws Exception
     */
    public function parseLogs(DateTime $dateTime, string $filePattern = null, bool $merge = false, string $level = 'ALL'): array
    {
        $parsedLogs = [];
        $this->errors = [];

        if ($filePattern !== null) {
            $formattedPattern = $filePattern === self::ALL_FILES ? "*" : "*$filePattern";
        } else {
            $formattedPattern = "*" . $dateTime->format($this->filePattern['date_format']) . ".log";
        }

        foreach ($this->getFiles($formattedPattern) as $fileInfo) {
            $parsedFile = $this->parseLogFile($fileInfo);

            if (!empty($parsedFile)) {
                $parsedLogs[$fileInfo->getFilename()] = $parsedFile;
            }
        }

        if ($merge) {
            $parsedLogs = array_merge([], ...array_values($parsedLogs));
        }
        
        if( $level !== 'ALL' ) {
            $parsedLogs = array_filter($parsedLogs, function($log) use ($level) {
                return $log['level']=== $level; 
            });
        }

        return $parsedLogs;
    }

    /**
     * Parse the content of a file
     * and return it in an array.
     *
     * @param SplFileInfo $logFile
     *
     * @return array
     */
    private function parseLogFile(SplFileInfo $logFile): array
    {
        $parsedFile = [];

        $file = new \SplFileObject($logFile->getRealPath());

        // Loop until we reach the end of the file.
        while (!$file->eof()) {
            try {
                $parsedLine = $this->parseLine($file->fgets());

                if (!empty($parsedLine)) {
                    $parsedFile[] = $parsedLine;
                }

            } catch (Exception $exception) {
                $this->errors[] = [
                    'log_file' => $logFile->getRealPath(),
                    'error' => $exception->getMessage()
                ];
            }
        }

        // Unset the file to call __destruct(), closing the file handle.
        $file = null;

        return $parsedFile;
    }

    /**
     * Parse a log string with regex.
     *
     * @param string $lineToParse
     *
     * @return array
     * @throws Exception
     */
    private function parseLine(string $lineToParse): array
    {
        $parsedLine = [];

        $success = preg_match('/^'.$this->logPattern.'$/', $lineToParse, $parsedLine);

        if (false === $success) {
            throw new Exception('Error during log parsing !');
        }

        // If 'groups' is configured, we keep only groups defined in it.
        if (!empty($this->groupsConfig)) {
            foreach ($parsedLine as $key => $match) {

                if (!key_exists($key, $this->groupsConfig)) {
                    unset($parsedLine[$key]);
                }
            }
        }

        return $parsedLine;
    }
}
