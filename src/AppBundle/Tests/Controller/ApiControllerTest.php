<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase {

    public function testContactsWithoutAuthentication() {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/api/contacts');
        
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'requested session not authenticated');
    }
    
    public function testContactsWithAuthentication() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', '/api/contacts');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals(count($response['data']), 0);
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactsAdd() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', '/api/contacts');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals(count($response['data']), 0);
        
        $em = $container->get('doctrine')->getManager();
        
        $crawler = $client->request('POST', '/api/contacts', array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "sample", "email":"another@email.com", "phone" : "12345678"}'
            );
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['name'], 'sample');
        $this->assertEquals($response['data']['email'], 'another@email.com');
        $this->assertEquals($response['data']['phone'], '12345678');
        
        $crawler = $client->request('GET', '/api/contacts');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals(count($response['data']), 1);
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
}
