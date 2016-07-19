<?php
namespace JPush\Tests;

class PushPayloadTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        global $client;
        $this->payload = $client->push()
                                ->setPlatform('all')
                                ->addAllAudience()
                                ->setNotificationAlert('Hello JPush');

        $this->payload_without_audience = $client->push()
                                                 ->setPlatform('all')
                                                 ->setNotificationAlert('Hello JPush');
    }

    public function testSimplePushToAll() {
        $payload = $this->payload;
        $result = $payload->build();

        $this->assertTrue(is_array($result));
        $this->assertEquals(3, count($result));
        $this->assertArrayHasKey('platform', $result);
        $this->assertArrayHasKey('audience', $result);
        $this->assertArrayHasKey('notification', $result);

        $response = $payload->send();
        $this->assertEquals('200', $response['http_code']);
        $body = $response['body'];
        $this->assertTrue(is_array($body));
        $this->assertEquals(2, count($body));
        $this->assertArrayHasKey('sendno', $body);
        $this->assertArrayHasKey('msg_id', $body);
    }

    public function testSetPlatform() {
        $payload = $this->payload;

        $result = $payload->build();
        $this->assertEquals('all', $result['platform']);

        $result = $payload->setPlatform('ios')->build();
        $this->assertTrue(is_array($result['platform']));
        $this->assertEquals(1, count($result['platform']));
        $this->assertTrue(in_array('ios', $result['platform']));
    }

    public function testSetAudience() {
        $result = $this->payload->build();
        $this->assertEquals('all', $result['audience']);
    }

    public function testAddTag() {
        $payload = $this->payload_without_audience;
        $result = $payload->addTag('hello')->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['tag']));
        $this->assertEquals(1, count($audience['tag']));

        $result = $payload->addTag(array('jpush', 'jiguang'))->build();
        $this->assertEquals(3, count($result['audience']['tag']));
    }
    public function testAddTag2() {
        $payload = $this->payload_without_audience;
        $result = $payload->addTagAnd(array('jpush', 'jiguang'))->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['tag_and']));
        $this->assertEquals(2, count($audience['tag_and']));

        $result = $payload->addTagAnd('hello')->build();
        $this->assertEquals(3, count($result['audience']['tag_and']));
    }

    public function testAddTagAnd1() {
        $payload = $this->payload_without_audience;
        $result = $payload->addTagAnd('hello')->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['tag_and']));
        $this->assertEquals(1, count($audience['tag_and']));

        $result = $payload->addTagAnd(array('jpush', 'jiguang'))->build();
        $this->assertEquals(3, count($result['audience']['tag_and']));
    }
    public function testAddTagAnd2() {
        $payload = $this->payload_without_audience;
        $result = $payload->addTagAnd(array('jpush', 'jiguang'))->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['tag_and']));
        $this->assertEquals(2, count($audience['tag_and']));

        $result = $payload->addTagAnd('hello')->build();
        $this->assertEquals(3, count($result['audience']['tag_and']));
    }

    public function testAddRegistrationId1() {
        $payload = $this->payload_without_audience;
        $result = $payload->addRegistrationId('hello')->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['registration_id']));
        $this->assertEquals(1, count($audience['registration_id']));

        $result = $payload->addRegistrationId(array('jpush', 'jiguang'))->build();
        $this->assertEquals(3, count($result['audience']['registration_id']));
    }
    public function testAddRegistrationId2() {
        $payload = $this->payload_without_audience;
        $result = $payload->addRegistrationId(array('jpush', 'jiguang'))->build();
        $audience = $result['audience'];
        $this->assertTrue(is_array($audience['registration_id']));
        $this->assertEquals(2, count($audience['registration_id']));

        $result = $payload->addRegistrationId('hello')->build();
        $this->assertEquals(3, count($result['audience']['registration_id']));
    }

    public function testSetNotificationAlert() {
        $result = $this->payload->build();
        $notification = $result['notification'];
        $this->assertTrue(is_array($notification));
        $this->assertEquals(1, count($notification));
        $this->assertEquals('Hello JPush', $result['notification']['alert']);
    }

    public function testIosNotification() {
        $payload = $this->payload;
        $result = $payload->iosNotification()->build();
        $ios = $result['notification']['ios'];
        $this->assertTrue(is_array($ios));
        $this->assertEquals(3, count($ios));
        $this->assertArrayHasKey('alert', $ios);
        $this->assertArrayHasKey('sound', $ios);
        $this->assertArrayHasKey('badge', $ios);
        $this->assertEquals('', $ios['alert']);
        $this->assertEquals('', $ios['sound']);
        $this->assertEquals('+1', $ios['badge']);

        $result = $payload->iosNotification('hello')->build();
        $ios = $result['notification']['ios'];
        $this->assertEquals('hello', $result['notification']['ios']['alert']);
    }
    public function testIosNotificationWithArray() {
        $payload = $this->payload;
        $array = array(
            'sound' => 'hello jpush',
            'badge' => 2,
            'content-available' => true,
            'category' => 'jiguang',
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
            'invalid_key' => 'invalid_value'
        );
        $result = $payload->iosNotification('', $array)->build();
        $ios = $result['notification']['ios'];
        $this->assertEquals(6, count($ios));
        $this->assertFalse(array_key_exists('invalid_key', $ios));
    }

    public function testAndroidNotification() {
        $payload = $this->payload;
        $result = $payload->androidNotification()->build();
        $android = $result['notification']['android'];
        $this->assertTrue(is_array($android));
        $this->assertEquals(1, count($android));
        $this->assertArrayHasKey('alert', $android);
        $this->assertEquals('', $android['alert']);

        $result = $payload->androidNotification('hello')->build();
        $android = $result['notification']['android'];
        $this->assertEquals('hello', $result['notification']['android']['alert']);
    }
    public function testAndroidNotificationWithArray() {
        $payload = $this->payload;
        $array = array(
            'title' => 'hello jpush',
            'build_id' => 2,
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
            'invalid_key' => 'invalid_value'
        );
        $result = $payload->androidNotification('', $array)->build();
        $android = $result['notification']['android'];
        $this->assertEquals(4, count($android));
        $this->assertFalse(array_key_exists('invalid_key', $android));
    }

    public function testSetSmsMessage() {
        $payload = $this->payload;
        $result = $payload->setSmsMessage('Hello JPush')->build();
        $sms = $result['sms_message'];
        $this->assertTrue(is_array($sms));
        $this->assertEquals(2, count($sms));
        $this->assertEquals('Hello JPush', $sms['content']);
        $this->assertEquals(0, $sms['delay_time']);

        $result = $payload->setSmsMessage('Hello JPush', 666)->build();
        $this->assertEquals(666, $result['sms_message']['delay_time']);

        $result = $payload->setSmsMessage('Hello JPush', 86500)->build();
        $this->assertEquals(0, $result['sms_message']['delay_time']);
    }

    public function testMessage() {
        $payload = $this->payload;
        $result = $payload->message('Hello JPush')->build();
        $message = $result['message'];
        $this->assertTrue(is_array($message));
        $this->assertEquals(1, count($message));
        $this->assertEquals('Hello JPush', $message['msg_content']);

        $array = array(
            'title' => 'hello jpush',
            'content_type' => '',
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
            'invalid_key' => 'invalid_value'
        );
        $result = $payload->message('Hello JPush', $array)->build();
    }

    public function testOptions() {
        $payload = $this->payload;
        $result = $payload->options()->build();
        $this->assertFalse(array_key_exists('options', $result));

        $array = array(
            'sendno' => 100,
            'time_to_live' => 100,
            'override_msg_id' => 100,
            'big_push_duration' => 100
        );
        $result = $payload->options($array)->build();
        $options = $result['options'];
        $this->assertEquals(5, count($options));
        $this->assertArrayHasKey('apns_production', $options);
        $this->assertEquals(false, $options['apns_production']);

    }
}
