<?php

namespace Ace\BlogBundle\Controller;

use Ace\BlogBundle\Entity\Comment;
use Ace\BlogBundle\Form\CommentType;
use Ace\BlogBundle\Security\Acl\MaskBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ace\BlogBundle\Entity\Post;
use Ace\BlogBundle\Form\PostType;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Post controller.
 *
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT a FROM AceBlogBundle:Post a');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1) /*page number*/,
            10 /*limit per page*/
        );

        return $this->render('AceBlogBundle:Post:index.html.twig', array('pagination' => $pagination));
    }

    /**
     * Creates a new Post entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Post();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // creating the ACL
            $aclProvider = $this->get('security.acl.provider');

            //parent acl is ROLE_ADMIN that has MASK_MASTER on user class
            $parentACL = $aclProvider->findAcl(new ObjectIdentity('Ace\BlogBundle\Entity\User', 'Ace\BlogBundle\Entity\User'));

            $objectIdentity = ObjectIdentity::fromDomainObject($entity);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($this->getUser());

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $acl->setParentAcl($parentACL);
            $aclProvider->updateAcl($acl);

            return $this->redirect($this->generateUrl('post_show', array('id' => $entity->getId())));
        }

        return $this->render('AceBlogBundle:Post:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Post entity.
     *
     */
    public function newAction()
    {
        $entity = new Post();
        $form   = $this->createCreateForm($entity);

        return $this->render('AceBlogBundle:Post:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     */
    public function showAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $newComment = new Comment();
        $commentForm   = $this->createNewCommentForm($newComment, $id);
        $commentForm->handleRequest($request);

        $entity = $em->getRepository('AceBlogBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if ($commentForm->isValid()) {
            $newComment->setPost($entity);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newComment);
            $em->flush();

            // creating the ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($newComment);
            $acl = $aclProvider->createAcl($objectIdentity);
            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($this->getUser());

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);

            // inherit acl from post
            $parentACL = $aclProvider->findAcl($objectIdentity::fromDomainObject($entity));
            $acl->setParentAcl($parentACL);

            //deny access to post creator if he isn't the comment author
            if ($entity->getUser()->getId() != $this->getUser()->getId()) {
                $securityIdentity = UserSecurityIdentity::fromAccount($entity->getUser());
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_DENY);
            }

            $aclProvider->updateAcl($acl);
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AceBlogBundle:Post:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'comment_form' => $commentForm->createView()
        ));
    }

    /**
     * @param Comment $entity
     * @param int $postId
     * @return \Symfony\Component\Form\Form
     */
    private function createNewCommentForm(Comment $entity, $postId)
    {
        $form = $this->createForm(new CommentType(), $entity, array(
            'action' => $this->generateUrl('post_show', array('id' => $postId)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Add new comment'));

        return $form;
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AceBlogBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (false === $this->get('security.context')->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException();
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AceBlogBundle:Post:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Post entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AceBlogBundle:Post')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        if (false === $this->get('security.context')->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('post_edit', array('id' => $id)));
        }

        return $this->render('AceBlogBundle:Post:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AceBlogBundle:Post')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            if (false === $this->get('security.context')->isGranted('DELETE', $entity)) {
                throw new AccessDeniedException();
            }

            $aclProvider = $this->container->get('security.acl.provider');

            foreach ($entity->getComments() as $comment) {
                $aclProvider->deleteAcl(ObjectIdentity::fromDomainObject($comment));
            }

            $aclProvider->deleteAcl(ObjectIdentity::fromDomainObject($entity));

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post'));
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
