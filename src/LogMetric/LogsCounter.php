<?php

namespace Kira0269\LogViewerBundle\LogMetric;

class LogsCounter implements LogMetricInterface
{
    private string $title;
    private array $filters;
    private array $groupsConfig;

    private string $color;
    private string $icon = 'fa-info';

    private int $result = 0;

    /**
     * LogCounter constructor.
     *
     * @param array  $metric
     * @param array  $groupsConfig
     */
    public function __construct(array $metric, array $groupsConfig)
    {
        $this->title = $metric['title'];
        $this->filters = $metric['filters'];
        $this->groupsConfig = $groupsConfig;

        if (isset($metric['color'])) {
            $this->color = $metric['color'];
        }
        if (isset($metric['icon'])) {
            $this->icon = $metric['icon'];
        }
    }

    public function calculate(array $logs)
    {
        $this->result = 0;
        foreach ($logs as $log) {
            if ($this->matchFilters($log)) {
               $this->result++;
            }
        }
    }

    private function matchFilters(array $log): bool
    {
        $match = true;
        foreach ($this->filters as $filter => $conditions)
        {
            if (!isset($log[$filter])) {
                continue;
            }

            try {
                switch ($this->groupsConfig[$filter]['type']) {
                    case "date":
                        $date = new \DateTime($conditions[0]);
                        $match = $match && strpos($log[$filter], $date->format('Y-m-d')) !== false;
                        break;
                    case "json":
                        $match = $match && preg_match('/' . $conditions[0] . '/', $log[$filter]);
                        break;
                    case "text":
                    default:
                        $match = $match && in_array($log[$filter], $conditions);
                        break;
                }
            } catch (\Exception $e) {
                $match = false;
            }
        }
        return $match;
    }

    public function getResult(): int
    {
        return $this->result;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }
    
    public function getFilters(): array
    {
        return $this->filters;
    }

}