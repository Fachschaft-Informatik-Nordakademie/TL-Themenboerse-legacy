<?php

namespace App;

class ResponseCodes
{
    // GENERAL RESPONSE CODES
    public static string $SUCCESS = "success";
    public static string $UNKNOWN_ERROR = "unknown_error";
    public static string $VALIDATION_FAILED = "validation_failed";
    public static string $AUTHENTICATION_FAILED = "authentication_failed";
    public static string $UNAUTHENTICATED = "unauthenticated";
    public static string $INVALID_JSON = "invalid_json";

    // TOPIC RELATED
    public static string $TOPIC_NOT_FOUND = "topic_not_found";
    public static string $TOPIC_EDIT_PERMISSION_DENIED = "topic_edit_permission_denied";
    public static string $TOPIC_DELETE_PERMISSION_DENIED = "topic_delete_permission_denied";
    public static string $TOPIC_DELETE_ALREADY_ASSIGNED = "topic_delete_already_assigned";
    public static string $TOPIC_NOT_OPEN_FOR_APPLICATIONS = "topic_not_open_for_applications";

    // APPLICATION RELATED
    public static string $APPLICATION_NOT_FOUND = "application_not_found";
    public static string $APPLICATION_DENIED_OWN_TOPIC = "application_denied_own_topic";
    public static string $APPLICATION_DENIED_ALREADY_APPLIED = "application_denied_already_applied";
    public static string $APPLICATION_DENIED_ONLY_LDAP = "application_denied_only_ldap";
    public static string $APPLICATION_ACCEPT_PERMISSION_DENIED = "application_accept_permission_denied";
    public static string $APPLICATION_ACCEPT_TOPIC_NOT_OPEN = "application_accept_topic_not_open";

    // FILE RELATED
    public static string $FILE_NOT_FOUND = "file_not_found";
    public static string $FILE_UPLOAD_ERROR = "file_upload_error";

    // AUTHENTICATION RELATED
    public static string $INVALID_CREDENTIALS = "invalid_credentials";
    public static string $EMAIL_ALREADY_IN_USE = "email_already_in_use";
    public static string $PASSWORD_TOO_SHORT = "password_too_short";
    public static string $VERIFICATION_TOKEN_INVALID = "verification_token_invalid";
    public static string $VERIFICATION_TOKEN_EXPIRED = "verification_token_expired";
    public static string $EMAIL_NOT_VERIFIED = "email_not_verified";

    // USER PROFILE RELATED
    public static string $PROFILE_NOT_FOUND = "profile_not_found";

    // USER RELATED
    public static string $USER_NOT_FOUND = "user_not_found";
    public static string $CANT_EDIT_OWN_USER = "cant_edit_own_user";

    public static function makeResponse(string $code, ?array $extra = null)
    {
        $result = [
            "code" => $code,
        ];

        if ($extra) {
            $result = array_merge($result, $extra);
        }

        return $result;
    }
}
