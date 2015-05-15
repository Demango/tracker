<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Source;
use AppBundle\Entity\Transaction;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTransactionData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $sourceRepo = $this->container
            ->get('doctrine')
            ->getManager('default')
            ->getRepository('AppBundle:Source');
        $sources = $sourceRepo->findAll();

        $dates = array();
        for ($month=1; $month < 6; $month++) {
            for ($day=1; $day < 31; $day++) {
                array_push(
                    $dates,
                    new \DateTime(sprintf('2015/%s/%s', $month, $day))
                );
            }
        }
        $groups = array(
            'Food',
            'Electricity',
            'Water',
            'Gas'
        );

        for ($i=1; $i < 1000; $i++) {
            $transaction = new Transaction();
            $transaction->setTitle('transaction '.$i);
            $transaction->setValue(rand(-25, 25));
            $transaction->setGroup($groups[array_rand($groups, 1)]);
            $transaction->setTime($dates[array_rand($dates, 1)]);
            $transaction->setSource($sources[array_rand($sources, 1)]);

            $manager->persist($transaction);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
