<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Source;
use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');
        $sources = $sourceRepo->findAll();
        $translated = $this->get('translator')->trans('test');
        $choices = array();
        foreach ($sources as $source) {
            $choices[$source->getId()] = $source->getName();
        }

        $sourceForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('source_submit'))
            ->add('name', 'text')
            ->add('value', 'money', array('label' => 'Initial Value'))
            ->add('save', 'submit', array('label' => 'Add Source'))
            ->getForm();

        $transactionForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('transaction_submit'))
            ->add('title', 'text')
            ->add('value', 'money')
            ->add('group', 'text')
            ->add('source', 'choice', array('choices' => $choices))
            ->add('time', 'date', array(
                'input' => 'datetime',
                'widget' => 'choice',
                'data' => new \DateTime()
                )
            )
            ->add('save', 'submit', array('label' => 'Add Transaction'))
            ->getForm();

        return $this->render('AppBundle:default:index.html.twig',
            array(
                'sourceForm'      => $sourceForm->createView(),
                'transactionForm' => $transactionForm->createView(),
                'sources'         => $sources
            )
        );
    }

    /**
     * @Route("/source/create", name="source_submit")
     */
    public function sourceSubmit(Request $request)
    {
        if ($request->getMethod() !== 'POST' && !$request) {
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();

        $sourceForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('source_submit'))
            ->add('name', 'text')
            ->add('value', 'money', array('label' => 'Initial Value'))
            ->add('save', 'submit', array('label' => 'Add Source'))
            ->getForm();

        $sourceForm->handleRequest($request);

        $data = $sourceForm->getData();

        $source = new Source();
        $source->setName($data['name']);
        $source->setValue($data['value']);

        $em->persist($source);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/transaction/create", name="transaction_submit")
     */
    public function transactionSubmit(Request $request)
    {
        if ($request->getMethod() !== 'POST' && !$request) {
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');
        $sources = $sourceRepo->findAll();

        $choices = array();
        foreach ($sources as $source) {
            $choices[$source->getId()] = $source->getName();
        }

        $transactionForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('transaction_submit'))
            ->add('title', 'text')
            ->add('value', 'money')
            ->add('group', 'text')
            ->add('source', 'choice', array('choices' => $choices))
            ->add('time', 'date', array(
                'input' => 'datetime',
                'widget' => 'choice',
                'data' => new \DateTime()
                )
            )
            ->add('save', 'submit', array('label' => 'Add Transaction'))
            ->getForm();

        $transactionForm->handleRequest($request);
        $data = $transactionForm->getData();

        $source = $sourceRepo->findOneById($data['source']);
        $source->setValue($source->getValue() + $data['value']);

        $transaction = new Transaction();
        $transaction->setTitle($data['title']);
        $transaction->setValue($data['value']);
        $transaction->setGroup($data['group']);
        $transaction->setTime($data['time']);
        $transaction->setSource($source);

        $em->persist($source);
        $em->persist($transaction);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/transaction/edit/{transactionId}", name="transaction_edit")
     */
    public function editTransaction($transactionId)
    {
        $transactionRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Transaction');
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');
        $sources = $sourceRepo->findAll();

        $choices = array();
        foreach ($sources as $source) {
            $choices[$source->getId()] = $source->getName();
        }

        $transaction = $transactionRepo->findOneById($transactionId);

        $transactionForm = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'update_transaction',
                    array('transactionId' => $transactionId)
                )
            )
            ->add('title', 'text', array('data' => $transaction->getTitle()))
            ->add('value', 'money', array('data' => $transaction->getValue()))
            ->add('group', 'text', array('data' => $transaction->getGroup()))
            ->add('source', 'choice', array(
                    'choices' => $choices,
                    'data' => $transaction->getsource()->getId()
                )
            )
            ->add('time', 'date', array(
                'input' => 'datetime',
                'widget' => 'choice',
                'data' => $transaction->getTime()
                )
            )
            ->add('save', 'submit', array('label' => 'Edit Transaction'))
            ->add('delete', 'submit', array('label' => 'Delete Transaction'))
            ->getForm();

        return $this->render('AppBundle:default:edit_transaction.html.twig',
            array(
                'transactionForm' => $transactionForm->createView(),
                'transaction' => $transaction
            )
        );
    }

    /**
     * @Route("/transaction/update/{transactionId}", name="update_transaction")
     */
    public function updateTransaction(Request $request, $transactionId)
    {
        if ($request->getMethod() !== 'POST' && !$request) {
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $transactionRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Transaction');
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');
        $sources = $sourceRepo->findAll();

        $choices = array();
        foreach ($sources as $source) {
            $choices[$source->getId()] = $source->getName();
        }

        $transaction = $transactionRepo->findOneById($transactionId);

        $transactionForm = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'update_transaction',
                    array('transactionId' => $transactionId)
                )
            )
            ->add('title', 'text', array('data' => $transaction->getTitle()))
            ->add('value', 'money', array('data' => $transaction->getValue()))
            ->add('group', 'text', array('data' => $transaction->getGroup()))
            ->add('source', 'choice', array('choices' => $choices))
            ->add('time', 'date', array(
                'input' => 'datetime',
                'widget' => 'choice',
                'data' => $transaction->getTime()
                )
            )
            ->add('save', 'submit', array('label' => 'Edit Transaction'))
            ->add('delete', 'submit', array('label' => 'Delete Transaction'))
            ->getForm();

        $transactionForm->handleRequest($request);
        $data = $transactionForm->getData();

        $source = $sourceRepo->findOneById($data['source']);

        if ($transactionForm->get('delete')->isClicked())
        {
            $source->setValue($source->getValue() - $transaction->getValue());
            $em->remove($transaction);
            $em->persist($source);
            $em->flush();

            return $this->redirectToRoute('source_transactions',
                array(
                    'sourceId' => $data['source'],
                    'dayCount' => 30
                )
            );
        }

        $source->setValue(
            $source->getValue() +
            $data['value'] -
            $transaction->getValue()
        );

        $transaction->setTitle($data['title']);
        $transaction->setValue($data['value']);
        $transaction->setGroup($data['group']);
        $transaction->setTime($data['time']);
        $transaction->setSource($source);

        $em->persist($source);
        $em->persist($transaction);
        $em->flush();

        return $this->redirectToRoute('source_transactions',
            array(
                'sourceId' => $data['source'],
                'dayCount' => 30
            )
        );
    }

    /**
     * @Route("/sources/{sourceId}/{dayCount}", name="source_transactions")
     */
    public function sourceTransactions($sourceId, $dayCount)
    {
        $transactionRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Transaction');
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');

        $source = $sourceRepo->findOneById($sourceId);

        $start = new \DateTime();
        $start = $start->modify(sprintf('-%d day', $dayCount + 1))->format('Y-m-d');

        $query = $transactionRepo->createQueryBuilder('t')
            ->leftJoin('t.source', 's')
            ->where('s.id = :source_id')
            ->andWhere('t.time > :start')
            ->andWhere('t.time < :end')
            ->setParameter('source_id', $source->getId())
            ->setParameter('start', $start)
            ->setParameter('end', new \DateTime())
            ->orderBy('t.time', 'DESC')
            ->getQuery();

        $transactions = $query->getResult();

        return $this->render('AppBundle:default:list.html.twig',
            array(
                'transactions' => $transactions,
                'source'       => $source
            )
        );
    }

     /**
     * @Route("/source/edit/{sourceId}", name="source_edit")
     */
    public function editSource($sourceId)
    {
        $em = $this->getDoctrine()->getManager();
        $transactionRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Transaction');
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');

        $source = $sourceRepo->findOneById($sourceId);

        $sourceForm = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'update_source',
                    array('sourceId' => $sourceId)
                )
            )
            ->add('name', 'text', array('data' => $source->getName()))
            ->add('value', 'money',
                array(
                    'data' => $source->getValue(),
                    'label' => 'Current Value'
                )
            )
            ->add('save', 'submit', array('label' => 'Edit Source'))
            ->add('delete', 'submit', array('label' => 'Delete Source'))
            ->getForm();

        return $this->render('AppBundle:default:edit_source.html.twig',
            array('sourceForm' => $sourceForm->createView()));
    }

    /**
     * @Route("/source/update/{sourceId}", name="update_source")
     */
    public function updateSource(Request $request, $sourceId)
    {
        if ($request->getMethod() !== 'POST' && !$request) {
            return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $transactionRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Transaction');
        $sourceRepo = $this->getDoctrine()
            ->getRepository('AppBundle:Source');
        $source = $sourceRepo->findOneById($sourceId);

        $sourceForm = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'update_source',
                    array('sourceId' => $sourceId)
                )
            )
            ->add('name', 'text', array('data' => $source->getName()))
            ->add('value', 'money',
                array(
                    'data' => $source->getValue(),
                    'label' => 'Current Value'
                )
            )
            ->add('save', 'submit', array('label' => 'Edit Source'))
            ->add('delete', 'submit', array('label' => 'Delete Source'))
            ->getForm();

        $sourceForm->handleRequest($request);
        $data = $sourceForm->getData();

        if ($sourceForm->get('delete')->isClicked())
        {
            $transactions = $transactionRepo->findBySource($source);

            foreach ($transactions as $transaction) {
                $em->remove($transaction);
            }
            $em->remove($source);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        $source->setName($data['name']);
        $source->setValue($data['value']);

        $em->persist($source);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}
