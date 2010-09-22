<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetPaginator
{
  private $totalItems;
  private $orderBy;
  private $order;
  private $page;
  private $keywords;
  private $fullTextSearch;
  private $idType;
  private $id;
  private $onpub;

  function __construct($totalItems, $orderBy, $order, $page, $keywords, $fullTextSearch, $idType, $id, $onpub)
  {
    $this->totalItems = $totalItems;
    $this->orderBy = $orderBy;
    $this->order = $order;
    $this->page = $page;
    $this->keywords = $keywords;
    $this->fullTextSearch = $fullTextSearch;
    $this->idType = $idType;
    $this->id = $id;
    $this->onpub = $onpub;
  }

  public function display()
  {
    $currentPage = 1;

    $this->keywords = urlencode($this->keywords);

    if ($this->page) {
      $currentPage = $this->page;
    }

    if ($this->totalItems > ONPUBGUI_PDO_ROW_LIMIT) {
      $this->pages = $this->totalItems / ONPUBGUI_PDO_ROW_LIMIT;

      if (is_double($this->pages)) {
        $this->pages = (int)$this->pages;
        $this->pages++;
      }

      if ($currentPage > 1) {
        if ($this->orderBy && $this->order) {
          if ($this->keywords) {
            if ($this->fullTextSearch) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                . $this->keywords . '&amp;fullTextSearch='
                . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                . $this->keywords . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
          }
          else {
            if ($this->id) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                . ($currentPage - 1) . '&amp;' . $this->idType . '=' . $this->id . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
          }
        }
        else {
          if ($this->keywords) {
            if ($this->fullTextSearch) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                . $this->keywords . '&amp;fullTextSearch='
                . $this->fullTextSearch . '&amp;page=' . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                . $this->keywords . '&amp;page=' . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
          }
          else {
            if ($this->id) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                . ($currentPage - 1) . '&amp;' . $this->idType . '=' . $this->id . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                . ($currentPage - 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16"></a>');
            }
          }
        }
      }
      else {
        en('<img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_back.gif" align="top" alt="Previous Page" title="Previous Page" width="16" height="16">');
      }

      if ($this->pages > 11) {
        if ($currentPage > 6) {
          if ($this->orderBy && $this->order) {
            if ($this->keywords) {
              if ($this->fullTextSearch) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;fullTextSearch='
                  . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=1">1...</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=1">1...</a>');
              }
            }
            else {
              if ($this->id) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                  . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=1&amp;' . $this->idType . '=' . $this->id . '">1...</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                  . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=1">1...</a>');
              }
            }
          }
          else {
            if ($this->keywords) {
              if ($this->fullTextSearch) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;fullTextSearch='
                  . $this->fullTextSearch . '&amp;page=1">1...</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;page=1">1...</a>');
              }
            }
            else {
              if ($this->id) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=1&amp;' . $this->idType . '=' . $this->id . '">1...</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=1">1...</a>');
              }
            }
          }
        }
        else {
          en('1...');
        }
      }

      if ($this->pages < 11) {
        for ($j = 0; $j < $this->pages; $j++) {
          if (($j + 1) == $currentPage) {
            en('<strong>' . $currentPage . '</strong>');
          }
          else {
            if ($this->orderBy && $this->order) {
              if ($this->keywords) {
                if ($this->fullTextSearch) {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                    . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
                else {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;orderBy='
                    . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
              }
              else {
                if ($this->id) {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                    . $this->order . '&amp;page=' . ($j + 1) . '&amp;'
                    . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                }
                else {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                    . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
              }
            }
            else {
              if ($this->keywords) {
                if ($this->fullTextSearch) {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
                else {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;page='
                    . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
              }
              else {
                if ($this->id) {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                    . ($j + 1) . '&amp;' . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                }
                else {
                  en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                    . ($j + 1) . '">' . ($j + 1) . '</a>');
                }
              }
            }
          }
        }
      }
      else {
        if ($currentPage < 7) {
          for ($j = 0; $j < $this->pages; $j++) {
            if (($j + 1) == $currentPage) {
              en('<strong>' . $currentPage . '</strong>');
            }
            else {
              if ($j < 11) {
                if ($this->orderBy && $this->order) {
                  if ($this->keywords) {
                    if ($this->fullTextSearch) {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                        . $this->order . '&amp;page=' . ($j + 1) . '">'
                        . ($j + 1) . '</a>');
                    }
                    else {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;orderBy='
                        . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                    }
                  }
                  else {
                    if ($this->id) {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                        . $this->order . '&amp;page=' . ($j + 1) . '&amp;'
                        . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                    }
                    else {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                        . $this->order . '&amp;page=' . ($j + 1) . '">'
                        . ($j + 1) . '</a>');
                    }
                  }
                }
                else {
                  if ($this->keywords) {
                    if ($this->fullTextSearch) {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                    }
                    else {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;page='
                        . ($j + 1) . '">' . ($j + 1) . '</a>');
                    }
                  }
                  else {
                    if ($this->id) {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '&amp;' . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                    }
                    else {
                      en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                    }
                  }
                }
              }
            }
          }
        }
        else {
          if ($currentPage < $this->pages - 5) {
            for ($j = $this->page - 6; $j < $this->pages; $j++) {
              if (($j + 1) == $currentPage) {
                en('<strong>' . $currentPage . '</strong>');
              }
              else {
                if ($j < $currentPage + 5) {
                  if ($this->orderBy && $this->order) {
                    if ($this->keywords) {
                      if ($this->fullTextSearch) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '">'
                          . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;orderBy='
                          . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                    else {
                      if ($this->id) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '&amp;'
                          . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '">'
                          . ($j + 1) . '</a>');
                      }
                    }
                  }
                  else {
                    if ($this->keywords) {
                      if ($this->fullTextSearch) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;page='
                          . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                    else {
                      if ($this->id) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '&amp;' . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                  }
                }
              }
            }
          }
          else {
            for ($j = $this->pages - 11; $j < $this->pages; $j++) {
              if (($j + 1) == $currentPage) {
                en('<strong>' . $currentPage . '</strong>');
              }
              else {
                if ($j < $currentPage + 5) {
                  if ($this->orderBy && $this->order) {
                    if ($this->keywords) {
                      if ($this->fullTextSearch) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '">'
                          . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;orderBy='
                          . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                    else {
                      if ($this->id) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '&amp;'
                          . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order='
                          . $this->order . '&amp;page=' . ($j + 1) . '">'
                          . ($j + 1) . '</a>');
                      }
                    }
                  }
                  else {
                    if ($this->keywords) {
                      if ($this->fullTextSearch) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;page='
                          . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                    else {
                      if ($this->id) {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '&amp;' . $this->idType . '=' . $this->id . '">' . ($j + 1) . '</a>');
                      }
                      else {
                        en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($j + 1) . '">' . ($j + 1) . '</a>');
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      if ($this->pages > 11) {
        if ($currentPage + 5 < $this->pages) {
          if ($this->orderBy && $this->order) {
            if ($this->keywords) {
              if ($this->fullTextSearch) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;fullTextSearch='
                  . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . $this->pages . '">...' . $this->pages . '</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . $this->pages . '">...' . $this->pages . '</a>');
              }
            }
            else {
              if ($this->id) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                  . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                  . $this->pages . '&amp;' . $this->idType . '=' . $this->id . '">...' . $this->pages . '</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy='
                  . $this->orderBy . '&amp;order=' . $this->order . '&amp;page='
                  . $this->pages . '">...' . $this->pages . '</a>');
              }
            }
          }
          else {
            if ($this->keywords) {
              if ($this->fullTextSearch) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;fullTextSearch='
                  . $this->fullTextSearch . '&amp;page=' . $this->pages . '">...' . $this->pages . '</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords='
                  . $this->keywords . '&amp;page=' . $this->pages . '">...'
                  . $this->pages . '</a>');
              }
            }
            else {
              if ($this->id) {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                  . $this->pages . '&amp;' . $this->idType . '=' . $this->id . '">...' . $this->pages . '</a>');
              }
              else {
                en('<a href="index.php?onpub=' . $this->onpub . '&amp;page='
                  . $this->pages . '">...' . $this->pages . '</a>');
              }
            }
          }
        }
        else {
          en('...' . $this->pages . '');
        }
      }

      if ($currentPage == $this->pages) {
        en('<img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16">');
      }
      else {
        if ($this->orderBy && $this->order) {
          if ($this->keywords) {
            if ($this->fullTextSearch) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
          }
          else {
            if ($this->id) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($currentPage + 1) . '&amp;' . $this->idType . '=' . $this->id . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;orderBy=' . $this->orderBy . '&amp;order=' . $this->order . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
          }
        }
        else {
          if ($this->keywords) {
            if ($this->fullTextSearch) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;fullTextSearch=' . $this->fullTextSearch . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;keywords=' . $this->keywords . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
          }
          else {
            if ($this->id) {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($currentPage + 1) . '&amp;' . $this->idType . '=' . $this->id . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
            else {
              en('<a href="index.php?onpub=' . $this->onpub . '&amp;page=' . ($currentPage + 1) . '"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'action_forward.gif" align="top" alt="Next Page" title="Next Page" width="16" height="16"></a>');
            }
          }
        }
      }

      br (2);
    }
  }
}
?>