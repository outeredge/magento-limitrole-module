# magento-limitrole-module
User Role Extra Module for Magento 2 by outer/edge

This Module is to add additional filtering to the user creation from admin. This module will stops customers(non administrator) from being able to add users with <s>full access or</s> any access. 

Currently this module will prevent users from adding new users with any role, if the current user don't have full privilege('Magento_Backend::all'=allow).

