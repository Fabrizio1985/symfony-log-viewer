<?php

namespace Kira0269\LogViewerBundle\Controller;

use Kira0269\LogViewerBundle\LogMetric\LogMetrics;
use Kira0269\LogViewerBundle\LogParser\LogParserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(LogParserInterface $logParser, LogMetrics $logMetric): Response
    {
        try {
            $displayDate = new \DateTime($this->getParameter('kira_log_viewer.dashboard.date'));
            $metrics = $logMetric->getMetricsResults($logParser->parseLogs($displayDate, null, null, true));
        } catch (\Exception $e) {
            throw new InvalidConfigurationException('Bad date format. Check kira_log_viewer.dashboard.date configuration. ' . $e->getMessage());
        }

        return $this->render('@LogViewer/dashboard/index.html.twig', [
            'per_row' => $this->getParameter('kira_log_viewer.dashboard.metrics_per_row'),
            'date' => $displayDate->format('Y-m-d'),
            'metrics' => $metrics
        ]);
    }
}