<?php

namespace Drupal\watchdog_registry\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use function count;

/**
 * Class WatchdogRegistryForm.
 */
class WatchdogRegistryForm extends EntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Database
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    $instance->database = $container->get('database');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $test = $form['watchdog_test2'];

    $watchdog_registry = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getLabel(),
      '#description' => $this->t('Label for the Watchdog registry.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $watchdog_registry->id(),
      '#machine_name' => [
        'exists' => '\Drupal\watchdog_registry\Entity\WatchdogRegistry::load',
        'standalone' => TRUE,
        'source' => NULL,
      ],
      '#disabled' => FALSE,
    ];

    $form['type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getType(),
      '#description' => $this->t('Type of the message.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getMessage(),
      '#description' => $this->t('The logged message.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['function'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Function'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getFunction(),
      '#description' => $this->t('Function in which the message was logged.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getFile(),
      '#description' => $this->t('File in which the message was logged.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['line'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Line'),
      '#maxlength' => 255,
      '#default_value' => $watchdog_registry->getLine(),
      '#description' => $this->t('Line in file in which the message was logged.'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $wid = $this->getRequest()->query->get('wid');
    $isNew = $watchdog_registry->isNew();

    if ($isNew) {
      $result = $this->database->query('SELECT variables FROM {watchdog} WHERE wid=:wid', [':wid' => $wid])->fetchAll();

      if (count($result) > 0) {
        $variables = unserialize($result[0]->variables);

        foreach ($variables as $variableName => $variableValue) {
          $name = mb_substr($variableName, 1);

          if ($name !== 'backtrace_string') {
            $value = $variableValue;

            if ($name === 'message') {
              if (preg_match_all('~/\S+\.php~', $variableValue, $matches)) {
                if (isset($matches[0]) === TRUE) {
                  foreach ($matches[0] as $match) {
                    $path = $this->getRelativeSymlinkedPath($match);
                    $value = str_replace($match, $path, $value);
                  }
                }
              }
            }

            if ($name === 'file') {
              $value = $this->getRelativeSymlinkedPath($variableValue);
            }
            $form[$name]['#default_value'] = $value;
          }
        }
        $form['name']['#default_value'] = implode(
          ' ',
          [
            $form['type']['#default_value'],
            'in',
            $form['file']['#default_value'],
            'on line',
            $form['line']['#default_value'],
          ]
        );
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $watchdog_registry = $this->entity;
    $watchdog_registry->set('type', $form_state->getValue('type'));
    $watchdog_registry->set('message', $form_state->getValue('message'));
    $watchdog_registry->set('function', $form_state->getValue('function'));
    $watchdog_registry->set('file', $form_state->getValue('file'));
    $watchdog_registry->set('line', $form_state->getValue('line'));
    $status = $watchdog_registry->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Watchdog registry.', [
          '%label' => $watchdog_registry->label(),
        ]));

        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Watchdog registry.', [
          '%label' => $watchdog_registry->label(),
        ]));
    }
    $form_state->setRedirectUrl($watchdog_registry->toUrl('collection'));
  }

  /**
   * Private helper function to retrieve the relative path.
   */
  private function getRelativePath($from, $to) {
    $from = explode('/', $from);
    $to = explode('/', $to);
    $relPath = $to;

    foreach ($from as $depth => $dir) {
      // Find first non-matching dir.
      if ($dir === $to[$depth]) {
        // Ignore this directory.
        array_shift($relPath);
      }
      else {
        // Get number of remaining dirs to $from.
        $remaining = count($from) - $depth;

        if ($remaining > 1) {
          // Add traversals up to first matching dir.
          $padLength = (count($relPath) + $remaining - 1) * -1;
          $relPath = array_pad($relPath, $padLength, '..');

          break;
        }
        $relPath[0] = './' . $relPath[0];
      }
    }

    return implode('/', $relPath);
  }

  /**
   * Private helper function to retrieve the relative symlinked path.
   */
  private function getRelativeSymlinkedPath($variableValue) {
    $value = str_replace(DRUPAL_ROOT . '/', '', $variableValue);
    // Make sure we have the relative path within the drupal root.
    if ($value === $variableValue) {
      $finder = new Finder();
      $finder->files()->in(DRUPAL_ROOT)->followLinks()->name(basename($variableValue));

      foreach ($finder as $file) {
        $relativePath = $file->getRelativePathname();

        if (sha1_file($variableValue) === sha1_file($relativePath)) {
          $value = $relativePath;
        }
      }
    }
    // Make sure we have the relative path outside of the drupal root.
    if ($value === $variableValue) {
      $value = $this->getRelativePath(DRUPAL_ROOT . '/index.php', $variableValue);
    }

    return $value;
  }

}
