<?php

/*
    Необходимо доработать класс рассылки Newsletter,
    что бы он отправлял письма
    и пуш нотификации для юзеров из UserRepository. 
    
    За отправку имейла мы считаем вывод в консоль строки:
    "Email {email} has been sent to user {name}"
    За отправку пуш нотификации:
    "Push notification has been sent to user {name} with device_id {device_id}"
    
    Так же необходимо реализовать функциональность для валидации имейлов/пушей:
    1) Нельзя отправлять письма юзерам с невалидными имейлами
    2) Нельзя отправлять пуши юзерам с невалидными device_id. Правила валидации можете придумать сами.
    3) Ничего не отправляем юзерам у которых нет имен
    4) На одно и то же мыло/device_id - можно отправить письмо/пуш только один раз
    
    Для обеспечения возможности масштабирования системы (добавление новых типов отправок и новых валидаторов), 
    можно добавлять и использовать новые классы и другие языковые конструкции php в любом количестве
*/

class Newsletter
{
    private string $userName = 'userName';
    private string $subject = "тема письма";
    private string $message = "Текст сообщения";
    private array $sent = ['e-mail' => [], 'push' => []];


    public function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    public function validateDeviceId($device_id)
    {
        if (!isset($device_id) || empty($device_id)) {
            return false;
        }

        return preg_match("/(Ks\[dqweer4|vfehlfg43g)/", $device_id);
    }

    private function checkedEmail(array $user)
    {
        if (isset($user['email'])) $this->sent['e-mail'][] = $user['email'];

    }

    private function checkedDeviceId(array $user)
    {
        if (isset($user['device_id'])) $this->sent['push'][] = $user['device_id'];
    }

    private function checkNotificationIsSend($user)
    {

        if (isset($user['email']) && in_array($user['email'], $this->sent['e-mail']) ||
            isset($user['device_id']) && in_array($user['device_id'], $this->sent['push']))
            return false;

        return true;
    }

    public function sendEmail()
    {
        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();
        $to = '';
        $this->userName = '';
        foreach ($users as $user) {
            if (isset($user['name']) &&
                isset($user['email']) &&
                $this->validateEmail($user['email']) &&
                $this->checkNotificationIsSend($user)
            ) {
                $to .= '<' . $user['email'] . '>, ';
                $this->userName .= ' ' . $user['name'] . ',';
                $this->checkedEmail($user);
            }
        }
        return "Email {$to} has been sent to user(s){$this->userName}";
    }

    public function sendPush()
    {
        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();
        $devices_id = '';
        $this->userName = '';
        foreach ($users as $user) {

            if (isset($user['name'])
                && isset($user['device_id'])
                && $this->validateDeviceId($user['device_id'])
                && $this->checkNotificationIsSend($user)
            ) {
                $devices_id .= '<' . $user['device_id'] . '>, ';
                $this->userName .= '"' . $user['name'] . '",';
                $this->checkedDeviceId($user);
            }
        }

        return "Push notification has been sent to user {$this->userName} with device_id {$devices_id}";
    }

    public function send(): void
    {
        echo $this->sendEmail() . PHP_EOL;

        echo $this->sendPush() . PHP_EOL;
    }
}

class UserRepository
{
    public function getUsers(): array
    {
        return [
            [
                'name' => 'Ivan',
                'email' => 'ivan@test.com',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Peter',
                'email' => 'peter@test.com'
            ],
            [
                'name' => 'Mark',
                'device_id' => 'Ks[dqweer4'
            ],
            [
                'name' => 'Nina',
                'email' => '...'
            ],
            [
                'name' => 'Luke',
                'device_id' => 'vfehlfg43g'
            ],
            [
                'name' => 'Zerg',
                'device_id' => ''
            ],
            [
                'email' => '...',
                'device_id' => ''
            ]
        ];
    }


}

/**
 * Тут релизовать получение объекта(ов) рассылки Newsletter и вызов(ы) метода send()
 * $newsletter = //... TODO
 * $newsletter->send();
 * ...
 */

$newsletter = new Newsletter();
$newsletter->send();