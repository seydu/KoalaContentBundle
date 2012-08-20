<?php

namespace Koala\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class PageController extends SecuredController
{
    /**
     * @Template()
     */
    public function newAction(Request $request)
    {
        if (!$this->can_edit()) {
            throw new \Exception('Permission denied');
        }

        $form = $this->get('koala_content.menu_item.form.new');

        return array('form'=>$form->createView());
    }

    public function createAction(Request $request)
    {
        if (!$this->can_edit()) {
            throw new \Exception('Permission denied');
        }

        $form = $this->get('koala_content.menu_item.form.new');

        $form->bindRequest($request);

        if ($form->isValid()) {
            $menuItem = $form->getData();
            $this->get('koala_content.menu_item_manager')->updateMenuItem($menuItem);

            return $this->redirect($this->generateUrl(null, array('content'=>$menuItem->getContent())));
        }

        return $this->render('KoalaContentBundle:Page:new.html.twig', array('form'=>$form->createView()));
    }

    /**
     * @Template()
     */
    public function editAction($page_id)
    {
        if (!$this->can_edit()) {
            throw new \Exception('Permission denied');
        }

        $menuItem = $this->get('koala_content.page_manager')->findById($page_id)->getFirstMenuItem();
        
        $form = $this->get('koala_content.menu_item.form.edit');
        $form->setData($menuItem);

        return array('form'=>$form->createView());
    }

    public function updateAction(Request $request, $page_id)
    {
        if (!$this->can_edit()) {
            throw new \Exception('Permission denied');
        }

        $menuItem = $this->get('koala_content.page_manager')->findById($page_id)->getFirstMenuItem();

        $form = $this->get('koala_content.menu_item.form.edit');
        $form->setData($menuItem);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $menuItem = $form->getData();
            $this->get('koala_content.menu_item_manager')->updateMenuItem($menuItem);

            return $this->redirect($this->generateUrl(null, array('content'=>$menuItem->getContent())));
        }

        return $this->render('KoalaContentBundle:Page:edit.html.twig', array('form'=>$form->createView()));
    }

    public function deleteAction($page_id)
    {
        if (!$this->can_edit()) {
            throw new \Exception('Permission denied');
        }

        $page = $this->get('koala_content.page_manager')->findById($page_id);
        $this->get('koala_content.page_manager')->removePage($page);
        return $this->redirect($this->generateUrl(null, array(
            'route' => $this->get('koala_content.route_manager')->findByPattern('/')
        )));
    }

    /**
     * @Template()
     */
    public function showAction($contentDocument)
    {
        $regions = array();
        foreach ($contentDocument->getRegions() as $r) {
            $regions[$r->getName()] = $r->getContent();
        }

        $template = $this->get('layouts_provider')->getTemplate($contentDocument->getLayout());

        return $this->render('KoalaContentBundle:Page:show.html.twig',
            array('page' => $contentDocument, 'regions' => $regions, 'template' => $template, 'can_edit'=>$this->can_edit())
        );
    }
}
