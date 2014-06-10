<?php

namespace Icap\PadBundle\Listener;

use Icap\PadBundle\Entity\PadAggregate;
use Icap\PadBundle\Entity\PadOptions;
use Icap\PadBundle\Form\PadOptionsType;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Form\Factory\FormFactory as ClarolineFormFactory;
use Symfony\Component\Form\FormFactory as SymfonyFormFactory;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class PadListener extends ContainerAware
{
    private $symfonyFormFactory;
    private $clarolineFormFactory;
    private $request;
    private $resourceManager;
    private $router;
    private $templating;
    private $em;

    /**
     * @DI\InjectParams({
     *     "symfonyFormFactory"   = @DI\Inject("form.factory"),
     *     "clarolineFormFactory" = @DI\Inject("claroline.form.factory"),
     *     "requestStack"         = @DI\Inject("request_stack"),
     *     "resourceManager"      = @DI\Inject("claroline.manager.resource_manager"),
     *     "router"               = @DI\Inject("router"),
     *     "templating"           = @DI\Inject("templating"),
     *     "em"                   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(
        SymfonyFormFactory $symfonyFormFactory,
        ClarolineFormFactory $clarolineFormFactory,
        RequestStack $requestStack,
        ResourceManager $resourceManager,
        TwigEngine $templating,
        UrlGeneratorInterface $router,
        EntityManager $em
    )
    {
        $this->symfonyFormFactory = $symfonyFormFactory;
        $this->clarolineFormFactory = $clarolineFormFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->resourceManager = $resourceManager;
        $this->router = $router;
        $this->templating = $templating;
        $this->em = $em;
    }

    /**
     * @DI\Observe("plugin_options_Icappad")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onAdministrate(PluginOptionsEvent $event)
    {
        $padOptions = $this->getOptions();

        $form = $this->symfonyFormFactory->create(new PadOptionsType(), $padOptions);
        $content = $this->templating->render(
            'IcapPadBundle:Pad:plugin_options_form.html.twig', array(
                'form' => $form->createView()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_form_Icap_pad_aggregate")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->clarolineFormFactory->create(
            ClarolineFormFactory::TYPE_RESOURCE_RENAME,
            array(),
            new PadAggregate()
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'Icap_pad_aggregate'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_Icap_pad_aggregate")
     *
     * @param CreateResourceEvent $event
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onCreate(CreateResourceEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }

        $form = $this->clarolineFormFactory->create(
            ClarolineFormFactory::TYPE_RESOURCE_RENAME,
            array(),
            new PadAggregate()
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $pad = $form->getData();
            $event->setResources(array($pad));
            $event->stopPropagation();

            return;
        }

        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'Icap_pad_aggregate'
            )
        );
        $event->setErrorFormContent($content);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_Icap_pad_aggregate")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->resourceManager->delete($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_Icap_pad_aggregate")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->router->generate(
            'Icap_pads_list',
            array('aggregateId' => $event->getResource()->getId())
        );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    private function getOptions() 
    {
        $options = $this->em->getRepository('IcapPadBundle:PadOptions')->findAll();

        if($options != null) {
            $options = $options[0];
        }
        if($options == null) {
            $options = new PadOptions();
        }

        return $options;
    }
}
