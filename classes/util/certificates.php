<?php
// This file is part of My Certificates block for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class to get the user certificates
 *
 * @package    block_mycertificates
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mycertificates\util;

/**
 * Class to get the user certificates.
 *
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificates {

    /**
     * @var \stdClass $user The target user.
     */
    protected $user;

    /**
     * @var int $courseid The course id.
     */
    protected $courseid;

    /**
     * Certificates constructor.
     *
     * @param \stdClass $user
     * @param int $courseid
     */
    public function __construct($user, $courseid = null) {
        $this->user = $user;
        $this->courseid = $courseid;
    }

    /**
     * Returns all issued certificates from all certificates modules.
     *
     * @return array
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_all_certificates() {
        $simplecertificate = $this->get_from_simplecertificate();
        $customcert = $this->get_from_customcert();
        $coursecertificate = $this->get_from_coursecertificate();

        $allcerts = array_merge($simplecertificate, $customcert, $coursecertificate);

        if (!empty($allcerts)) {
            return array_values($this->group_certificates_by_course($allcerts));
        }

        return [];
    }

    /**
     * Get all issued certificates from simplecertificate module.
     *
     * @return array
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_from_simplecertificate() {
        global $DB;

        $simplecertificate = \core_plugin_manager::instance()->get_plugin_info('mod_simplecertificate');

        if (is_null($simplecertificate)) {
            return [];
        }

            $sql = "SELECT
                        sci.code,
                        sci.pathnamehash,
                        sc.name,
                        c.id as courseid,
                        c.fullname,
                        c.shortname,
                        'simplecertificate' as module
                    FROM {simplecertificate_issues} sci
                    INNER JOIN {simplecertificate} sc ON sc.id = sci.certificateid
                    INNER JOIN {course} c ON sc.course = c.id
                    WHERE sci.timedeleted IS NULL AND sci.userid = :userid";
        $params = ['userid' => $this->user->id];

        if ($this->courseid) {
            $sql .= ' AND c.id = :courseid';
            $params['courseid'] = $this->courseid;
        }

        $sql .= ' ORDER BY c.fullname, sci.timecreated';

        $certificates = $DB->get_records_sql($sql, $params);

        if (empty($certificates)) {
            return [];
        }

        $fs = get_file_storage();

        $returndata = [];
        foreach ($certificates as $certificate) {
            if (!$fs->file_exists_by_hash($certificate->pathnamehash)) {
                continue;
            }

            $url = new \moodle_url('/mod/simplecertificate/wmsendfile.php', [
                'code' => $certificate->code,
            ]);

            $certificate->downloadurl = $url->out(false);

            $returndata[] = $certificate;
        }

        return $returndata;
    }

    /**
     * Get issued certificates from customcert module.
     *
     * @return array
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_from_customcert() {
        global $DB;

        $customcert = \core_plugin_manager::instance()->get_plugin_info('mod_customcert');

        if (is_null($customcert)) {
            return [];
        }

        $sql = "SELECT
                  ci.customcertid,
                  cc.name,
                  c.id as courseid,
                  c.fullname,
                  c.shortname,
                  'customcert' as module
                FROM {customcert_issues} ci
                INNER JOIN {customcert} cc ON cc.id = ci.customcertid
                INNER JOIN {course} c ON c.id = cc.course
                WHERE ci.userid = :userid";

        $params = ['userid' => $this->user->id];

        if ($this->courseid) {
            $sql .= ' AND c.id = :courseid';
            $params['courseid'] = $this->courseid;
        }

        $sql .= ' ORDER BY c.fullname, ci.timecreated';

        $certificates = $DB->get_records_sql($sql, $params);

        if (empty($certificates)) {
            return [];
        }

        foreach ($certificates as $certificate) {
            $url = new \moodle_url('/mod/customcert/my_certificates.php', [
                'downloadcert' => true,
                'userid' => $this->user->id,
                'certificateid' => $certificate->customcertid,
            ]);

            $certificate->downloadurl = $url->out(false);
        }

        return $certificates;
    }

    /**
     * Get issued certificates from Moodle HQ Certificate plugin.
     *
     * @return array
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_from_coursecertificate() {
        global $DB;

        $certificateplugin = \core_plugin_manager::instance()->get_plugin_info('tool_certificate');
        $coursecertificateplugin = \core_plugin_manager::instance()->get_plugin_info('coursecertificate');

        if (is_null($certificateplugin)) {
            return [];
        }

        $sql = "SELECT
                  ci.id,
                  ci.code,
                  t.name,
                  IFNULL(ci.courseid, 1) as courseid,
                  c.fullname,
                  c.shortname,
                  'coursecertificate' as module
                FROM {tool_certificate_issues} ci
                INNER JOIN {tool_certificate_templates} t ON t.id = ci.templateid
                INNER JOIN {course} c ON c.id = IFNULL(ci.courseid, 1)
                WHERE ci.userid = :userid";

        $params = ['userid' => $this->user->id];

        if ($this->courseid) {
            $sql .= ' AND c.id = :courseid';
            $params['courseid'] = $this->courseid;
        }

        $sql .= ' ORDER BY c.fullname, ci.timecreated';

        $certificates = $DB->get_records_sql($sql, $params);

        if (empty($certificates)) {
            return [];
        }

        foreach ($certificates as $certificate) {
            if (!$coursecertificateplugin) {
                $certificate->module = 'tool_certificate';
            }
            $certificate->downloadurl = \tool_certificate\template::view_url($certificate->code)->out(false);
        }

        return $certificates;
    }

    /**
     * Group certificates by course.
     *
     * @param array $certificates
     *
     * @return array
     */
    public static function group_certificates_by_course($certificates) {
        global $PAGE;

        $returndata = [];

        foreach ($certificates as $certificate) {
            $certs = [$certificate];
            if (isset($returndata[$certificate->courseid])) {
                $certs = array_merge($certs, $returndata[$certificate->courseid]['certificates']);

                $returndata[$certificate->courseid]['certificates'] = $certs;

                continue;
            }

            $returndata[$certificate->courseid] = [
                'courseid' => $certificate->courseid,
                'shortname' => format_string($certificate->shortname, true, ['context' => $PAGE->context]),
                'fullname' => format_string($certificate->fullname, true, ['context' => $PAGE->context]),
                'certificates' => $certs,
            ];
        }

        return $returndata;
    }
}
