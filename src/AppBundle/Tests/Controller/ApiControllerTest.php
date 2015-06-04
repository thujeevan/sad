<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase {

    public function testContactsWithoutAuthentication() {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/api/contacts');
        
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
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
    
    public function testContactWithoutAuthentication() {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/api/contact/12');
        
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'requested session not authenticated');
    }
    
    public function testContactWithAuthentication() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', '/api/contact/');
        
        $this->assertNotEquals(401, $client->getResponse()->getStatusCode());
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactWithoutId() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', '/api/contact/');
        
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactWithInvalidId() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', '/api/contact/null');
        
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid contact requested');
        
        $crawler = $client->request('GET', '/api/contact/abc');
        
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid contact requested');
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactWithWrongId() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        // create new contact
        $crawler = $client->request('POST', '/api/contacts', array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "another sample", "email":"another@email.com", "phone" : "12345678"}'
            );
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        $contactId = $response['data']['id'];
        // logout
        $container->get('security.context')->setToken(NULL);
        
        // signup as another user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"testanother@email.com", "password" : "1234"}'
        );
        
        $crawler = $client->request('GET', "/api/contact/{$contactId}");
        
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'Invalid contact requested');
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user1 = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user1);
        $user2 = $em->getRepository('AppBundle:User')->loadUserByUsername('testanother@email.com');
        $em->remove($user2);
        $em->flush();
    }
    
    public function testContactGetSuccess() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        // create new contact
        $crawler = $client->request('POST', '/api/contacts', array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "another sample", "email":"another@email.com", "phone" : "12345678"}'
            );
        
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        $contactId = $response['data']['id'];
        
        $crawler = $client->request('GET', "/api/contact/{$contactId}");
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['id'], $contactId);
        $this->assertEquals($response['data']['name'], 'another sample');
        $this->assertEquals($response['data']['email'], 'another@email.com');
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactPut() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        // create new contact
        $crawler = $client->request('POST', '/api/contacts', array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "another sample", "email":"another@email.com", "phone" : "12345678"}'
            );
        
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        $contactId = $response['data']['id'];
        
        $crawler = $client->request('PUT', "/api/contact/{$contactId}", array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "", "email":"another@email.com", "phone" : "12345678"}'
        );
        
        $this->assertEquals(422, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'failed');
        $this->assertEquals($response['reason'], 'required field(s) missing');
        
        $crawler = $client->request('PUT', "/api/contact/{$contactId}", array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "another updated user", "email":"another@email.com", "phone" : "12345678"}'
        );
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data']['id'], $contactId);
        $this->assertEquals($response['data']['name'], 'another updated user');
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
    
    public function testContactDelete() {
        $client = static::createClient();
        $container = $client->getContainer();
        
        // signup implicitly logging in user
        $crawler = $client->request(
                'POST', '/session/signup', array(), array(), array('CONTENT_TYPE' => 'application/json'), '{"email":"another@email.com", "password" : "1234"}'
        );
        // create new contact
        $crawler = $client->request('POST', '/api/contacts', array(), array(), 
                array('CONTENT_TYPE' => 'application/json'), 
                '{"name" : "another sample", "email":"another@email.com", "phone" : "12345678"}'
            );
        
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        $contactId = $response['data']['id'];
        
        $crawler = $client->request('DELETE', "/api/contact/{$contactId}");
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), TRUE);
        
        $this->assertEquals($response['type'], 'success');
        $this->assertEquals($response['data'], []);
        
        $em = $container->get('doctrine')->getManager();
        
        // removed created user for test consistency 
        $user = $em->getRepository('AppBundle:User')->loadUserByUsername('another@email.com');
        $em->remove($user);
        $em->flush();
    }
}
