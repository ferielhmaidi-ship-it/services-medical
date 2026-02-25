<?php
require 'vendor/autoload.php';
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->load(__DIR__ . '/.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$repo = $em->getRepository(\App\Entity\TempsTravail::class);

$data = $repo->findAll();
echo "--- TempsTravail Dump ---\n";
foreach ($data as $tt) {
    echo sprintf(
        "ID: %d | Doctor: %d | Day: %s | Specific Date: %s | Hours: %s - %s\n",
        $tt->getId(),
        $tt->getDoctorId(),
        $tt->getDayOfWeek(),
        $tt->getSpecificDate() ? $tt->getSpecificDate()->format('Y-m-d') : 'RECURRING',
        $tt->getStartTime()->format('H:i'),
        $tt->getEndTime()->format('H:i')
    );
}
echo "--- End of Dump ---\n";
