<?php

namespace Icap\PadBundle\Controller;

use Icap\PadBundle\Entity\PadAggregate;
use Icap\PadBundle\Entity\Pad;
use Icap\PadBundle\Entity\PadOptions;
use Icap\PadBundle\Form\PadType;
use Icap\PadBundle\Form\PadOptionsType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class PadController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $pagerFactory;
    private $securityContext;
    private $translator;
    private $utils;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "securityContext"     = @DI\Inject("security.context"),
     *     "translator"          = @DI\Inject("translator"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PagerFactory $pagerFactory,
        SecurityContextInterface $securityContext,
        EventDispatcherInterface $eventDispatcher,
        Translator $translator,
        Utilities $utils,
        WorkspaceManager $workspaceManager
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->pagerFactory = $pagerFactory;
        $this->securityContext = $securityContext;
        $this->translator = $translator;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/pad/{aggregateId}/list",
     *     name = "icap_pads_list"
     * )
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="IcapPadBundle:PadAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("IcapPadBundle:Pad:padsList.html.twig")
     *
     * @return Response
     */
    public function padsListAction(PadAggregate $aggregate)
    {
        $collection = new ResourceCollection(array($aggregate->getResourceNode()));

        $padAggregate = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository("IcapPadBundle:PadAggregate")
            ->findOneBy(array("id" => $aggregate->getId()))
        ;

        return array(
            '_resource' => $aggregate,
            'resourceCollection' => $collection,
            'pads' => $padAggregate->getPads()
        );
    }

    /**
     * @EXT\Route(
     *     "/pad/{aggregateId}/create",
     *     name = "icap_pad_create"
     * )
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="IcapPadBundle:PadAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("IcapPadBundle:Pad:createForm.html.twig")
     *
     * @param PadAggregate $aggregate
     *
     * @return Response
     */
    public function createFormAction(Request $request, PadAggregate $aggregate)
    {
        if (false === $this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $endpointRoot = $this->getOptions()->getEndpointRoot();
        $form = $this->formFactory->create(new PadType($endpointRoot), new Pad());

        $method = $request->getMethod();
        if ($method == "GET") {
            return array(
                'type'          => 'create',
                'form'          => $form->createView(),
                'endpoint_root' => $endpointRoot,
                '_resource'     => $aggregate
            );
        } else if ($method == "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->get('security.context')->getToken()->getUser();
                $pad = $form->getData();
                $pad->setPadOwner($user->getMail());

                $url = sprintf("%s/api/create", $endpointRoot);
                $answer = $this->httpPost($url, $pad->toArray());
                $message = json_decode($answer, true);
                $this->addFlashBagMessage($message);

                if ($message['type'] == 'error') {
                    return $this->redirect($this->generateUrl(
                        'icap_pad_create',
                        array('aggregateId'=> $aggregate->getId())
                    ));
                } else {
                    $this->addPad($message, $aggregate);

                    return $this->redirect($this->generateUrl(
                        'icap_pads_list',
                        array('aggregateId'=> $aggregate->getId())
                    ));
                }
            } else {
                return array(
                    'type'          => 'create',
                    'form'          => $form->createView(),
                    'endpoint_root' => $endpointRoot,
                    '_resource'     => $aggregate
                );
            }
        }
    }

    /**
     * Edit Options for pads
     *
     * @EXT\Route("/edit_options", name="icap_pads_edit_options")
     * @EXT\Method("POST")
     */
    public function editPadOptionsAction(Request $request)
    {
        if (false === $this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $padOptions = $this->getOptions();

        $form = $this->formFactory->create(new PadOptionsType(), $padOptions);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $padOptions = $form->getData();
            $em->persist($padOptions);
            $em->flush();

            return new RedirectResponse($this->generateUrl('claro_admin_plugins'));
        }

        return $this->render(
            'IcapPadBundle:Pad:plugin_options_form.html.twig', array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * Get a padOptions
     * 
     * @return \Icap\PadBundle\Entity\PadOptions
     */
    private function getOptions()
    {
        $options = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('IcapPadBundle:PadOptions')
            ->findAll()
        ;

        if($options != null) {
            $options = $options[0];
        }
        if($options == null) {
            $options = new PadOptions();
        }

        return $options;
    }
    
    /**
     * Do a http post request
     * 
     * @param string $url
     * @param array $params
     * @return string
     */
    private function httpPost($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $output=curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Add a new pad entity in database
     * 
     * @param array $message containing pad informations
     * @param Icap\PadBundle\Entity\PadAggregate $aggregate
     */
    private function addPad($message, $aggregate)
    {
        $newPad = new Pad();
        if (isset($message ['private_url'])) {
            $padUrl = $message ['private_url'];
        } else {
            $padUrl = $message ['public_url'];
        }
        $newPad->hydrate(
            $padUrl,
            $message['title'],
            $message['program'],
            $message['unit'],
            $message['padOwner'],
            $aggregate,
            $message['padUsers']
        );
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($newPad);
        $em->flush();
    }

    /**
     * Add a flashbag message
     * 
     * @param type $message
     */
    private function addFlashBagMessage($message)
    {
        $this->get('session')->getFlashBag()->add($message['type'], $message['content']);
    }
}