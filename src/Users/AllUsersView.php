<?php
declare(strict_types=1);

/**
 * View for all users
 *
 * View to render the list of all users.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Users;

use GmossoEndpoint\Mvc\View\AbstractView;

class AllUsersView extends AbstractView
{
    /**
     * @inheritDoc
     */
    public function html(): string
    {
        $users = $this->viewData->data();

        $tableRows = '';
        $notFoundString = __('value not found', 'gmosso-endpoint');
        $userFields = ['name', 'id', 'username'];
        $rowNumber = 0;
        foreach ($users as $user) {
            $userId = $user['id'] ??= null;
            if (is_null($userId) || !is_int($userId)) {
                continue;
            }

            foreach ($userFields as $userField) {
                $user[$userField] ??= $notFoundString;
            }

            ++$rowNumber;
            $tableRows .= $this->tableRow($rowNumber, $user);
        }
        return '<div id="table-items">' . $this->tableHeader() . $tableRows . '</div>';
    }

    /**
     * @inheritDoc
     */
    public function renderJson(): void
    {
        wp_send_json($this->prepareJsonOutput());
    }

    /**
     * Returns the html markup for the table header.
     *
     * @since  1.0.0
     * @return string table header markup
     */
    private function tableHeader(): string
    {
        $tableHeader = '<div class="table-cell heading">Name</div>';
        $tableHeader .= '<div class="table-cell heading">Id</div>';
        $tableHeader .= '<div class="table-cell heading last">Username</div>';

        return $tableHeader;
    }

    /**
     * Returns the html markup for a table row.
     *
     * @since  1.0.0
     * @param  int    $rowNumber progressive row number
     * @param  array  $user      user data for current row
     * @return string            row markup
     */
    private function tableRow(int $rowNumber, array $user): string
    {
        $classDark = ($rowNumber % 2 === 0) ? ' dark' : '';

        $row = "<div class=\"table-cell{$classDark} name\">" .
            $this->detailsLink($user['id'], $user['name']) .
            '</div>';

        $row .= "<div class=\"table-cell{$classDark}\">" .
            $this->detailsLink($user['id'], (string)$user['id']) .
            '</div>';

        $row .= "<div class=\"table-cell{$classDark} last\">" .
            $this->detailsLink($user['id'], $user['username']) .
            '</div>';

        return $row;
    }

    /**
     * Returns the html markup for the link to click to have user details.
     *
     * @since  1.0.0
     * @param  int    $userId   id of the user
     * @param  string $linkText text for the link
     * @return string           link markup
     */
    private function detailsLink(int $userId, string $linkText): string
    {
        $html = "<a href=\"\" data-userid=\"{$userId}\">";
        $html .= $linkText;
        $html .= '</a>';

        return $html;
    }
}
