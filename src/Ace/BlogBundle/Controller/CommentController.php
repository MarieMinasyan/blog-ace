<?php

namespace Ace\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ace\BlogBundle\Entity\Comment;
use Ace\BlogBundle\Form\CommentType;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Comment controller.
 *
 */
class CommentController extends Controller
{
    /**
     * Displays a form to edit an existing Comment entity.
     *
     */
    public function editAction($postId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AceBlogBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $editForm = $this->createEditForm($entity, $postId);

        return $this->render('AceBlogBundle:Comment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'postId'      => $postId
        ));
    }

    /**
     * @param Comment $entity
     * @param $postId
     * @return \Symfony\Component\Form\Form
     */
    private function createEditForm(Comment $entity, $postId)
    {
        $form = $this->createForm(new CommentType(), $entity, array(
            'action' => $this->generateUrl('comment_update', array('postId' => $postId,'id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Comment entity.
     *
     */
    public function updateAction(Request $request, $postId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AceBlogBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $editForm = $this->createEditForm($entity, $postId);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('id' => $postId)));
        }

        return $this->render('AceBlogBundle:Comment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'postId'      => $postId
        ));
    }

    /**
     * @param $postId
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction($postId, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AceBlogBundle:Comment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $aclProvider = $this->container->get('security.acl.provider');
        $aclProvider->deleteAcl(ObjectIdentity::fromDomainObject($entity));

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('post_show', array('id' => $postId)));
    }

}
