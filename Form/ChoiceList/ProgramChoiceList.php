<?php

namespace Icap\PadBundle\Form\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class ProgramChoiceList extends LazyChoiceList
{
    protected $endpointRoot;

    public function __construct ($endpointRoot)
    {
        $this->endpointRoot = $endpointRoot;
    }

    protected function loadChoiceList()
    {
        $ch = curl_init();
        $url = sprintf('%s/api/program-list', $this->endpointRoot);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if (!$output) {
            throw new \Exception("The endpoint parameter is wrong, not set, or maybe the pad manager is down");
        }
        $json = json_decode(utf8_encode($output), true);

        curl_close($ch);

        $choices = array();
        foreach ($json['items'] as $program) {
            $choices[$program['id']] = $program['name'];
        }

        return new SimpleChoiceList($choices);
    }
}