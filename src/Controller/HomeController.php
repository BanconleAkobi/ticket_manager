<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use function PHPUnit\Framework\arrayHasKey;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder, TicketRepository $repository): Response
    {


        //création du chart pour le graphique affichant le nombre de tickets par mois.
        $tickets = $repository->findBy(['created_by' => $this->getUser()]);
        $createByMonths = array_fill(1, 12, 0);
        foreach ($tickets as $ticket) {
            $actualMonth = (int)date("m", ($ticket->getCreatedAt())->getTimestamp()) ;
            $createByMonths[$actualMonth]++;
        }


        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            'datasets' => [
                [
                'label' => 'Nombre de tickets créés',
                'backgroundColor' =>'rgb(255, 99, 132)',
                'borderColor' => 'rgb(255, 99, 132)',
                'data' => array_values($createByMonths)
                    ]
            ]
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => max($createByMonths) + 1,
                    'ticks' => [
                        'stepSize' => 1,
                    ]
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ]);

        /*
         * case 1: ouvert, 2: en cours ,3: résolu, 4: fermé
         */

        $NbByStatus = [
            'OPEN' => 0,
            'IN_PROGRESS' => 0,
            'RESOLVED' => 0,
            'CLOSED' => 0,
        ];
        foreach ($tickets as $ticket) {
            $NbByStatus[$ticket->getStatus()->value]++;
        }

        //graphique des nombres de tickets en fonction des status
        $chart2 =  $chartBuilder->createChart(Chart::TYPE_RADAR);

        $chart2->setData([
            'labels' => ['Ouvert', 'En Cours', 'Résolu', 'Fermé'], // Labels des statuts
            'datasets' => [
                [
                    'label' => 'Tickets par statut',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_values($NbByStatus)
                ]
            ]
        ]);
        $chart2->setOptions([
            'scales' => [
                'r' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => max($NbByStatus) + 1,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top', // Légende en haut du graphique
                ],
            ],
        ]);



        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('home/index.html.twig', [
            'chart' => $chart,
            'chart2' => $chart2,
            'nbByStatus' => $NbByStatus,
        ]);
    }
}
