<?php
namespace Kira0269\LogViewerBundle\Controller;

use Kira0269\LogViewerBundle\LogParser\LogParserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewerController extends AbstractController
{

    public function index(Request $request, LogParserInterface $logParser): Response
    {
        $dates = $logParser->getFilesDates();

        return $this->render('@LogViewer/viewer/index.html.twig', [
            'dates' => $dates,
            'groups' => $this->getParameter('kira_log_viewer.groups')
        ]);
    }

    public function ajax(Request $request, LogParserInterface $logParser): JsonResponse
    {
        if ($request->query->has('dateFilterFrom')) {

            $dateFilterFrom = $request->query->has('dateFilterFrom') ? \DateTime::createFromFormat('Y-m-d\TH:i', $request->query->get('dateFilterFrom'), new \DateTimeZone('Europe/Rome')) : null;
            $dateFilterTo = $request->query->has('dateFilterTo') ? \DateTime::createFromFormat('Y-m-d\TH:i', $request->query->get('dateFilterTo'), new \DateTimeZone('Europe/Rome')) : null;
            
            $level = $request->query->has('level') ? $request->query->get('level') : 'ALL';

            $logs = $logParser->parseLogs($dateFilterFrom, $dateFilterTo, true, $level);

            return $this->json($logs);
        } else {
            return $this->json('');
        }
    }
}
