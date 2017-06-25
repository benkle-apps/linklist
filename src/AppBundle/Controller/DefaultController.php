<?php

namespace AppBundle\Controller;

use AppBundle\Service\UrlQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    const PAGE_SIZE = 2000;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl($this->getUser() ? 'app_list_urls' : 'login'));
    }

    /**
     * @Route("/list/{page}", name="app_list_urls", defaults={"page": 1}, requirements={"page": "\d+"})
     * @Route("/list/{domain}/{page}", name="app_list_urls_by_domain", defaults={"domain": null, "page": 1},
     *                                 requirements={"domain":
     *                                 "[\w-]+(\.[\w-]+)+|local", "page": "\d+"})
     * @param Request $request
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function domainListAction(Request $request, $page, $domain = null)
    {
        $urlManager = $this->get('app.url_manager');

        $domains = $urlManager->getDomains($this->getUser());

        $urlQuery =
            $urlManager->getQuery()
                       ->setUser($this->getUser())
                       ->setDomain($domain)
                       ->setGoneHandling(UrlQuery::ALL);

        $total = $urlQuery->count();
        $pageCount = ceil($total / (double)self::PAGE_SIZE);

        $urlQuery->setLimit(self::PAGE_SIZE)
                 ->setOffset(($page - 1) * self::PAGE_SIZE);
        $urls = $urlQuery->get();

        $variables = [
            'urls'          => $urls,
            'total'         => $total,
            'currentDomain' => $domain,
            'domains'       => $domains,
        ];

        $jsonData = $this->get('app.serializer')->json($variables, ['display']);

        if (
            $request->getContentType() == 'json' ||
            in_array('application/json', $request->getAcceptableContentTypes()) ||
            $request->isXmlHttpRequest()
        ) {
            return new JsonResponse($this->get('app.serializer')->json($variables, ['display']));
        } else {
            return $this->render(
                'default/list.html.twig', [
                                            'jsonData' => $jsonData,
                                        ]
            );
        }
    }
}
