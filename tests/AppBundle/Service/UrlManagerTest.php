<?php

namespace AppBundle\Service;

use AppBundle\Entity\Url;
use AppBundle\Entity\User;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use League\Uri\UriParser;

class UrlManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testAddUrlWithInstance()
    {
        $userMock    =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser   =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('persist')
                    ->with($this->isInstanceOf(Url::class));

        $urlManager = new UrlManager($managerMock, $uriParser);
        $urlManager->addUrl($userMock, 'http://www.test.local');
    }

    public function testAddUrlValueSetting()
    {
        $that      = $this;
        $userMock  =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $uriParser->expects($this->once())
                  ->method('parse')
                  ->with('http://www.test.local')
                  ->will($this->returnValue(['host' => 'www.test.local']));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('persist')
                    ->with($this->isInstanceOf(Url::class))
                    ->will(
                        $this->returnCallback(
                            function (Url $url) use ($that, $userMock) {
                                $that->assertEquals('test.local', $url->getDomain());
                                $that->assertEquals('http://www.test.local', $url->getUrl());
                                $that->assertEquals($userMock, $url->getUser());
                            }
                        )
                    );

        $urlManager = new UrlManager($managerMock, $uriParser);
        $urlManager->addUrl($userMock, 'http://www.test.local');
    }

    public function testAddUrlFileUrl()
    {
        $that      = $this;
        $userMock  =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $uriParser->expects($this->once())
                  ->method('parse')
                  ->with('file:///www/test/local')
                  ->will($this->returnValue(['host' => '']));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('persist')
                    ->with($this->isInstanceOf(Url::class))
                    ->will(
                        $this->returnCallback(
                            function (Url $url) use ($that, $userMock) {
                                $that->assertEquals('local', $url->getDomain());
                                $that->assertEquals('file:///www/test/local', $url->getUrl());
                                $that->assertEquals($userMock, $url->getUser());
                            }
                        )
                    );

        $urlManager = new UrlManager($managerMock, $uriParser);
        $urlManager->addUrl($userMock, 'file:///www/test/local');
    }

    public function testGetUrl()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager = new UrlManager($managerMock, $uriParser);
        $urlManager->getUrls($userMock, new GetUrlsOptions());
    }

    public function testGetUrlWithLimit()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setMaxResults')
                         ->with(1);
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager     = new UrlManager($managerMock, $uriParser);
        $options        = new GetUrlsOptions();
        $options->limit = 1;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithOffset()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setFirstResult')
                         ->with(1);
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager      = new UrlManager($managerMock, $uriParser);
        $options         = new GetUrlsOptions();
        $options->offset = 1;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithDomain()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->any())
                         ->method('setParameter')
                         ->withConsecutive(
                             ['user', $userMock],
                             ['domain', 'test.local']
                         )
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager      = new UrlManager($managerMock, $uriParser);
        $options         = new GetUrlsOptions();
        $options->domain = 'test.local';
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithGoneOnly()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('andWhere')
                         ->with(
                             $this->logicalAnd(
                                 $this->stringContains('gone'),
                                 $this->stringContains('true')
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager    = new UrlManager($managerMock, $uriParser);
        $options       = new GetUrlsOptions();
        $options->gone = GetUrlsOptions::GONE_ONLY;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithGoneNot()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('andWhere')
                         ->with(
                             $this->logicalAnd(
                                 $this->stringContains('gone'),
                                 $this->stringContains('false')
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager    = new UrlManager($managerMock, $uriParser);
        $options       = new GetUrlsOptions();
        $options->gone = GetUrlsOptions::GONE_NOT;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithVisitedOnly()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('andWhere')
                         ->with(
                             $this->logicalAnd(
                                 $this->stringContains('visited'),
                                 $this->stringContains('not null')
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager       = new UrlManager($managerMock, $uriParser);
        $options          = new GetUrlsOptions();
        $options->visited = GetUrlsOptions::VISITED_ONLY;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlWithVisitedNot()
    {
        $userMock         =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser        =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('andWhere')
                         ->with(
                             $this->logicalAnd(
                                 $this->stringContains('visited'),
                                 $this->stringContains('null'),
                                 $this->logicalNot($this->stringContains('not null'))
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue(
                                 $this->getMockBuilder(AbstractQuery::class)
                                      ->disableOriginalConstructor()
                                      ->getMock()
                             )
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager       = new UrlManager($managerMock, $uriParser);
        $options          = new GetUrlsOptions();
        $options->visited = GetUrlsOptions::VISITED_NOT;
        $urlManager->getUrls($userMock, $options);
    }

    public function testGetUrlsSimple()
    {
        $that     = $this;
        $userMock =
            $this->getMockBuilder(User::class)
                 ->getMock();

        $urlManager =
            $this->getMockBuilder(UrlManager::class)
                 ->disableOriginalConstructor()
                 ->setMethods(['getUrls', '__destruct'])
                 ->getMock();
        $urlManager->expects($this->once())
                   ->method('getUrls')
                   ->with($userMock, $this->isInstanceOf(GetUrlsOptions::class))
                   ->will(
                       $this->returnCallback(
                           function (User $u, GetUrlsOptions $o) use ($that) {
                               $that->assertEquals('test', $o->domain);
                               $that->assertEquals(1, $o->limit);
                               $that->assertEquals(2, $o->offset);

                               return 3;
                           }
                       )
                   );
        $i = $urlManager->getUrlsSimple($userMock, 'test', 1, 2);
        $this->assertEquals(3, $i);
    }

    public function testCountUrl()
    {
        $userMock  =
            $this->getMockBuilder(User::class)
                 ->getMock();
        $uriParser =
            $this->getMockBuilder(UriParser::class)
                 ->getMock();
        $queryMock =
            $this->getMockBuilder(AbstractQuery::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryMock->expects($this->once())
                  ->method('getScalarResult')
                  ->will($this->returnValue([1, 2, 3]));
        $queryBuilderMock =
            $this->getMockBuilder(QueryBuilder::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('getQuery')
                         ->will(
                             $this->returnValue($queryMock)
                         );
        $queryBuilderMock->expects($this->atLeastOnce())
                         ->method('setParameter')
                         ->with('user', $userMock)
                         ->will($this->returnSelf());
        $queryBuilderMock->expects($this->any())
                         ->method($this->anything())
                         ->will($this->returnSelf());
        $repoMock =
            $this->getMockBuilder(EntityRepository::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $repoMock->expects($this->once())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($queryBuilderMock));
        $managerMock =
            $this->getMockBuilder(EntityManager::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $managerMock->expects($this->once())
                    ->method('getRepository')
                    ->with('AppBundle:Url')
                    ->will($this->returnValue($repoMock));

        $urlManager = new UrlManager($managerMock, $uriParser);
        $c          = $urlManager->countUrls($userMock, new GetUrlsOptions());
        $this->assertEquals(1, $c);
    }
}
