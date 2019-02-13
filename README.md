## Build hooks drupal module

13 Feb 2019: At the moment this module is a heavily modified version of the [Build hooks](https://www.drupal.org/project/build_hooks) module in drupal.org).
The plan is to integrate this version in the official one, but for now this lives in this github repository.

### Description and use case

This module is meant to be used in cases where Drupal is used as a content repository for one or more frontend sites.
The typical use case is for Gatsby sites powered by the [Gatsby Drupal source plugin](https://www.gatsbyjs.org/packages/gatsby-source-drupal/): in this type of setup Drupal holds the content and a separate Gatsby site is built using the Drupal installation as the content source.

This module mainly provides privileged users with a UI to:

- Trigger deployments of the connected static site(s) at will
- View a log of the content that has been created, modified or deleted since the last deployment

#### Installation and configuration

1. Install the module as usual
1. Visit the path `/admin/config/build_hooks/settings` and select the types of entities that you want to include in the log. For example: content (nodes), media entities, etc. As a general rule, it makes sense to select here the entities that are used in the static front end site. (For example, logging changes to user entities is probably not required)
(at the present time, some of the settings here are not functional: disregard the Triggers and Messages section) (@TODO: Cleanup or adapt those functions)
1. (optional) If you want to connect to CircleCI environments, enable the `build_hooks_circleci` module, and insert your CircleCI api key at the page: `/admin/config/build_hooks_circleci/buildhookscircleciconfig`
(PLEASE NOTE:) in context where the site configuration is committed to git, consider instead getting that value from environment variables, for example adding this to your `settings.php`:
    ```
    $config['build_hooks_circleci.circleCiConfig']['circleci_api_key'] = getenv('YOUR_ENV_VAR_HERE');
    ```
1. Go to `/admin/structure/frontend_environment` and add one or more frontend environments, filling the required data for each. You can choose the type of environment.
1. The toolbar now should show your environments (clear cache if not visible @TODO: Clear toolbar cache on plugin creation)
1. Edit some content: you will see that the counter next to each environment toolbar item will increment
1. When ready to trigger a deployment, click on the toolbar item for the environment, and you will be taken to a page where you will see the changelog and a button to trigger the deployment.

#### Environment types

The `build_hooks` base module comes with a "Generic" environment type.
The `build_hooks_circleci` submodule provides an environment type for circle ci.

Environments are defined as plugins: check the circle ci submodule to see how you can create your own plugin type.
