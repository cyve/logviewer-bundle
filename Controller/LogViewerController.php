<?php

namespace Cyve\LogViewerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class LogViewerController extends AbstractController
{
    /**
     * @Route("/logs", name="cyve_log_viewer", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $files = array_map('basename', glob($this->getParameter('kernel.logs_dir').DIRECTORY_SEPARATOR.$this->getParameter('kernel.environment').'*.log'));

        return $this->render('@CyveLogViewer/index.html.twig', ['files' => $files]);
    }

    /**
     * @Route("/logs/{file}", name="cyve_log_viewer_logs", methods={"GET"})
     */
    public function logs(string $file, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $logDir = $this->getParameter('kernel.logs_dir');
        $environment = $this->getParameter('kernel.environment');
        $logFiles = glob($logDir.DIRECTORY_SEPARATOR.$environment.'*.log');
        $filename = realpath($logDir.DIRECTORY_SEPARATOR.$file);
        if (!$filename || !in_array($filename, $logFiles)) {
            throw new NotFoundHttpException();
        }

        $qChannel = $request->query->get('channel');
        $qLevel = $request->query->get('level');
        $qDistinct = $request->query->get('distinct');

        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        $channels = [];
        $messages = [];
        $logs = [];

        $lines = array_reverse(file($filename, FILE_SKIP_EMPTY_LINES));
        foreach ($lines as $line) {
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([a-z_]+).([A-Z]+): (.*) ([\{|\[].*[\}\]]) (\[\])$/', $line, $matches);
            $date = new \DateTime($matches[1]);
            $channel = $matches[2];
            $level = strtolower($matches[3]);
            $message = $matches[4];
            $context = json_decode($matches[5]);

            if ($qChannel && $channel !== $qChannel || $qLevel && $level !== $qLevel || $qDistinct && in_array($message, $messages)) {
                continue;
            }
            $channels[] = $channel;
            $messages[] = $message;

            $logs[] = [
                'date' => $date,
                'channel' => $channel,
                'level' => $level,
                'message' => $message,
                'context' => $context,
            ];
        }

        return $this->render('@CyveLogViewer/logs.html.twig', [
            'title' => $file,
            'logs' => $logs,
            'channels' => array_unique($channels),
            'levels' => $levels,
        ]);
    }
}
