<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class StaffCreatePage extends CRUDPage
{
    private ?Staff $staff;
    private ?array $errors = [];
    private ?array $rooms = [];
    private int $state;

    protected function prepare(): void
    {
        BasePage::redirectIfNotLogged();
        
        parent::prepare();
        $this->findState();
        $this->title = "Založit nového zaměstnance";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            //jdi dál
            $this->staff = new Staff();
            $stmt = PDOProvider::get()->prepare("SELECT room_id, `name`, `no` FROM room ORDER BY `no`");
            $stmt->execute([]);
            $this->rooms = $stmt->fetchAll();
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            //načti je
            $this->staff = Staff::readPost(self::ACTION_INSERT);

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = $this->staff->validate($this->errors, self::ACTION_INSERT);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                //ulož je
                $success = $this->staff->insert();

                //přesměruj je
                $this->redirect(self::ACTION_INSERT, $success);
            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'staffForm',
            [
                'staff' => $this->staff,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
                'create' => true //TODO: Staff restore login and password addition for insert, also dobre rano doufam ze jsi se dobre vyspal <3
            ]
        );
    }

    private function findState() : void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }
}

$page = new StaffCreatePage();
$page->render();

?>
