<?php
declare(strict_types=1);

/**
 * View for a single user
 *
 * View to render the data of a single user.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Users;

use GmossoEndpoint\Mvc\View\AbstractView;

class SingleUserView extends AbstractView
{
    /**
     * @inheritDoc
     */
    public function html(): string
    {
        $user = $this->viewData->data();

        $addressData = $user['address'];
        $address = "{$addressData['suite']} - {$addressData['street']}";
        $address .= " {$addressData['city']}";
        $address .= " {$addressData['zipcode']}.";
        $address .= " Coordinates(lat, lng):";
        $address .= " ({$addressData['geo']['lat']}, {$addressData['geo']['lng']})";

        $companyData =  $user['company'];
        $company = "{$companyData['name']}:";
        $company .= " {$companyData['catchPhrase']} - {$companyData['bs']}";

        $details = $this->fieldMarkup('id', (string)$user['id']);
        $details .= $this->fieldMarkup('name', $user['name']);
        $details .= $this->fieldMarkup('username', $user['username']);
        $details .= $this->fieldMarkup('email', $user['email']);
        $details .= $this->fieldMarkup('address', $address);
        $details .= $this->fieldMarkup('phone', $user['phone']);
        $details .= $this->fieldMarkup('website', $user['website']);
        $details .= $this->fieldMarkup('company', $company);

        return $details;
    }

    /**
     * @inheritDoc
     */
    public function renderJson(): void
    {
        wp_send_json($this->prepareJsonOutput());
    }

    /**
     * Returns the html markup for a user details field.
     *
     * @since  1.0.0
     * @param  string $fieldName  name of the field
     * @param  string $fieldValue value of the field
     * @return string             field markup
     */
    private function fieldMarkup(string $fieldName, string $fieldValue): string
    {
        $markup = '<span class="field-name">' . $fieldName . '</span>';
        $markup .= ": {$fieldValue}<br />";

        return $markup;
    }
}
