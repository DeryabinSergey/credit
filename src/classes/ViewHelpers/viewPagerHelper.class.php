<?php

    class viewPagerHelper extends viewBaseHelper
    {
        protected $page = null;
        protected $anchor = null;
        protected $perPage = null;
        protected $count = null;
        protected $diff = 2;
        protected $module = null;
        protected $params = array();
        protected $enableArrows = true;
        protected $pageVar = 'page';

        /**
         * @param string $count
         */
        public function __construct($count)
        {
                $this->count = $count;
        }

        /**
         * @param int $count
         * @return viewPagerHelper
         */
        public static function create($count)
        {
                return new self($count);
        }

        public function setEnableArrows($enableArrows = true)
        {
                $this->enableArrows = ($enableArrows === true);
                return $this;
        }

        public function isEnableArrows()
        {
                return $this->enableArrows;
        }

        /**
         * @param int $page
         * @return viewPagerHelper
         */
        public function setPage($page)
        {
                $this->page = $page;
                return $this;
        }

        public function getPage()
        {
                return $this->page;
        }

        public function setPageVar($var)
        {
                $this->pageVar = $var;
                return $this;
        }

        public function getPageVar()
        {
                return $this->pageVar;
        }

        /**
         * @param int $perPage
         * @return viewPagerHelper
         */
        public function setPerPage($perPage)
        {
                $this->perPage = $perPage;
                return $this;
        }

        public function getPerPage()
        {
                return $this->perPage;
        }

        /**
         * @param int $diff
         * @return viewPagerHelper
         */
        public function setDiff($diff)
        {
                $this->diff = $diff;
                return $this;
        }

        public function getDiff()
        {
                return $this->diff;
        }
        
        public function setAnchor($anchor)
        {
                $this->anchor = $anchor;
                return $this;
        }

        public function getAnchor()
        {
                return $this->anchor;
        }

        /**
         * @param string $module
         * @return viewPagerHelper
         */
        public function setModule($module)
        {
                $this->module = $module;
                return $this;
        }

        public function getModule()
        {
                return $this->module;
        }

        /**
         * @param array $params
         * @return viewPagerHelper
         */
        public function setParams($params = array())
        {
                $this->params = $params;
                return $this;
        }

        public function getParams()
        {
                return $this->params;
        }

        /**
         * @return string
         */
        public function __toString()
        {
                if ($this->getPageCount() > 1) {
                        $this->setHelperPath(self::$helperPath);
                        return parent::__toString();
                } else return '';
        }

        /**
         * @return int
         */
        public function getPageCount()
        {
                return ceil($this->count / $this->perPage);
        }

        /**
         * @return int
         */
        public function getCurrentPage()
        {
                return $this->page;
        }

        /**
         * @return bool
         */
        public function isNext()
        {
                return $this->isEnableArrows() && $this->getCurrentPage() < $this->getPageCount();
        }

        /**
         * @return bool
         */
        public function isPrevious()
        {
                return $this->isEnableArrows() && (($this->getCurrentPage() - 1) > 0);
        }
    }
?>