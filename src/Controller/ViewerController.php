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
        $filePattern = $request->query->has('file') ? $request->query->get('file') : null;
        $files = $logParser->getFiles($filePattern);
        $dates = $logParser->getFilesDates();

        return $this->render('@LogViewer/viewer/index.html.twig', [
            'files' => $files,
            'dates' => $dates,
            'groups' => $this->getParameter('kira_log_viewer.groups'),
        ]);
    }

    public function ajax(Request $request, LogParserInterface $logParser): JsonResponse
    {
        $dateParts = [];
        $date = null;
        foreach (['year', 'month', 'day'] as $param) {
            if (!$request->query->has($param)) {
                $date = new \DateTime();
                break;
            }
            $dateParts[] = $request->query->get($param);
        }
        if(null === $date) {
            $date = \DateTime::createFromFormat('Y-m-d', implode('-', $dateParts));
        }
        
        $level = $request->query->has('level') ? $request->query->get('level') : 'ALL';
        
        $filePattern = $request->query->has('file') ? $request->query->get('file') : null;

        $logs = $logParser->parseLogs($date, $filePattern, true, $level);

        return $this->json($logs);
    }
}
