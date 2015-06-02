<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase {

    public function testLogin() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/session/login');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Address book")')->count() > 0);
    }

    
    public function testIsauthenticated() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/session/isauthenticated');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'requested session not authenticated');
    }
    
    public function testSignup() {
        $client = static::createClient();
        
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"", "password" : "blabla"}'
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid email or password, please double check and try again');
        
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"sample@sample.com", "password" : ""}'
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid email or password, please double check and try again');
        
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"", "password" : ""}'
        );
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid email or password, please double check and try again');
        
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['username'], 'another@email.com');
        
        $crawler = $client->request('GET', '/session/isauthenticated');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['username'], 'another@email.com');

        $container = $client->getContainer();
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }

    public function testLoginCheck() {
        $client = static::createClient();
        
        $crawler = $client->request(
                'POST', '/session/login_check', array('email' => '', 'password' => ''), array(), array(
                    'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                    'HTTP_X-Requested-With' => 'XMLHttpRequest'
                )
        );
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertNotEmpty($response['reason']);
        
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $container = $client->getContainer();
        $container->get('security.context')->setToken(NULL);
        
        $crawler = $client->request(
                'POST', '/session/login_check', array('email' => 'another@email.com', 'password' => '1234'), array(), array(
                    'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                    'HTTP_X-Requested-With' => 'XMLHttpRequest'
                )
        );
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['username'], 'another@email.com');
        
        $crawler = $client->request('GET', '/session/isauthenticated');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['username'], 'another@email.com');
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
}
