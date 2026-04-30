-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 29, 2026 at 10:31 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `athar`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(10) NOT NULL,
  `adminName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminName`, `email`, `password`) VALUES
(1, 'athar', 'admin@athar.com', '$2y$10$0HOmd0.AV2AeS5p77x49k.Iu4EW9rCBrerWsOtAofty6l3MIZAaeW');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `courseID` int(5) NOT NULL,
  `courseCode` varchar(20) DEFAULT NULL,
  `courseName` varchar(100) NOT NULL,
  `courseDescription` text NOT NULL,
  `level` int(1) NOT NULL,
  `track` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`courseID`, `courseCode`, `courseName`, `courseDescription`, `level`, `track`) VALUES
(2, 'IT 210', 'المبادئ الأساسية لتقنية المعلومات', 'يتناول هذا المقرر المفاهيم الأساسية في تقنية المعلومات، ويشمل مقدمة في أنظمة الحاسب والشبكات والبرمجيات وقواعد البيانات.', 3, 'عام'),
(3, 'IT 219', 'الفيزياء لتقنية المعلومات', 'يدرس هذا المقرر المبادئ الفيزيائية المرتبطة بتقنية المعلومات، ويشمل الكهرباء والمغناطيسية والدوائر الإلكترونية وأساسيات أشباه الموصلات.', 3, 'عام'),
(4, 'Math 151', 'الرياضيات المحددة', 'يغطي هذا المقرر موضوعات الرياضيات التي تخدم علوم الحاسب، ويشمل المنطق الرياضي والمجموعات والعلاقات والدوال والجبر البولياني.', 3, 'عام'),
(5, 'CSC 111', 'برمجة حاسبات 1', 'مقرر تأسيسي في البرمجة يُعرّف الطالبة بمفاهيم الخوارزميات وحل المشكلات، يشمل المتغيرات والتعبيرات والجمل الشرطية والحلقات والمصفوفات.', 3, 'عام'),
(6, 'IT 223', 'تنظيم وعمارة الحاسبات', 'يتناول هذا المقرر بنية الحاسب الداخلية وطريقة عمله، ويشمل تمثيل البيانات وأنظمة الأعداد والمعالج المركزي والذاكرة ووحدات الإدخال والإخراج.', 4, 'عام'),
(7, 'IT 222', 'مبادئ قواعد البيانات', 'يُعرّف هذا المقرر الطالبةَ بمفاهيم قواعد البيانات العلائقية وتصميمها، يشمل مخطط ER ولغة SQL ومبادئ تطبيع قواعد البيانات.', 4, 'عام'),
(8, 'IT 214', 'تصميم تجربة المستخدم', 'يتناول هذا المقرر مبادئ تصميم واجهات المستخدم وتجربة الاستخدام UX/UI، يشمل البحث مع المستخدمين والنماذج الأولية واختبار قابلية الاستخدام.', 4, 'عام'),
(10, 'IT 328', 'مبادئ شبكات الحاسب', 'يغطي هذا المقرر أساسيات شبكات الحاسب ويشمل نموذجي OSI وTCP/IP وبروتوكولات الاتصال وأنواع الشبكات وعنونة IP والتوجيه والتبديل.', 5, 'عام'),
(11, 'IT 326', 'تنقيب البيانات', 'يتناول هذا المقرر تقنيات استخراج المعرفة من البيانات الضخمة، ويشمل التصنيف والتجميع وقواعد الارتباط وخوارزميات الاكتشاف.', 5, 'عام'),
(12, 'IT 324', 'أمن المعلومات', 'يُعرّف هذا المقرر الطالبةَ بمفاهيم أمن المعلومات، ويشمل التشفير والمصادقة والتهديدات الشائعة وسياسات الأمن وإدارة المخاطر.', 5, 'عام'),
(13, 'IT 312', 'هندسة تطبيقات الويب', 'يتناول هذا المقرر تطوير تطبيقات الويب من الجانبين الأمامي والخلفي، يشمل HTML وCSS وJavaScript وتقنيات الخادم وقواعد البيانات.', 5, 'عام'),
(14, 'CSC 212', 'تراكيب البيانات', 'يدرس هذا المقرر بنى تخزين البيانات، ويشمل القوائم المترابطة والمكدسات والطوابير والأشجار والرسوميات وجداول التجزئة وتحليل الخوارزميات.', 5, 'عام'),
(15, 'IT 320', 'هندسة البرمجيات', 'يغطي هذا المقرر دورة حياة تطوير البرمجيات، ويشمل منهجية Agile ومراحل التحليل والتصميم والتطوير والاختبار ونمذجة الأنظمة بـ UML.', 6, 'عام'),
(16, 'IT 423', 'مقدمة إدارة مشاريع تقنية المعلومات', 'يُعرّف هذا المقرر الطالبةَ بمبادئ إدارة المشاريع في بيئة تقنية المعلومات، يشمل التخطيط والجدولة والمراقبة وإدارة المخاطر وأساسيات PMBOK.', 7, 'عام'),
(17, 'IT 426', 'أساسيات الذكاء الاصطناعي', 'يُقدّم هذا المقرر مدخلاً شاملاً للذكاء الاصطناعي، ويشمل خوارزميات البحث والاستدلال والشبكات العصبية والتعلم الآلي وتطبيقاته.', 7, 'عام'),
(18, 'IT 496', 'مشروع تخرج 1', 'المرحلة الأولى من مشروع التخرج، تشمل تحديد مشكلة تقنية واقعية ومراجعة الأدبيات وتحليل المتطلبات وتصميم الحل وإعداد التقرير الأولي.', 7, 'عام'),
(19, 'IT 497', 'مشروع تخرج 2', 'المرحلة الثانية من مشروع التخرج، تشمل تنفيذ الحل المصمم واختباره وتوثيقه والعرض النهائي أمام لجنة متخصصة.', 8, 'عام'),
(20, 'IT 427', 'ريادة الأعمال والإبداع في تقنية المعلومات', 'يجمع هذا المقرر بين ريادة الأعمال وتقنية المعلومات، ويشمل التفكير التصميمي ونموذج Canvas والشركات الناشئة وتحويل الأفكار لمشاريع قابلة للتطبيق.', 8, 'عام'),
(21, 'IT 362', 'مبادئ علم البيانات', 'يُقدّم هذا المقرر أساسيات علم البيانات ودورة حياتها، ويشمل جمع البيانات وتنظيفها واستكشافها وتحليلها باستخدام Python وPandas وNumPy.', 6, 'علم البيانات والذكاء الاصطناعي'),
(22, 'IT 329', 'تقنيات الويب المتقدمة', 'يتوسع هذا المقرر في تطوير تطبيقات الويب، ويشمل إطارات العمل الحديثة وتصميم RESTful API والتطبيقات أحادية الصفحة SPA.', 6, 'عام'),
(23, 'Math 244', 'الجبر الخطي', 'يدرس هذا المقرر موضوعات الجبر الخطي وتطبيقاتها، ويشمل المصفوفات والمحددات وأنظمة المعادلات الخطية والقيم الذاتية والمتجهات الذاتية.', 6, 'عام'),
(24, 'CSC 227', 'نظم التشغيل', 'يدرس هذا المقرر بنية نظم التشغيل، ويشمل إدارة العمليات والجدولة وإدارة الذاكرة وأنظمة الملفات والتزامن ومنع الإغلاق المتبادل.', 6, 'عام'),
(25, 'IT 461', 'تعلم الآلة التطبيقي', 'يتناول هذا المقرر تقنيات التعلم الآلي وتطبيقها على مسائل واقعية، ويشمل خوارزميات التعلم الخاضع للإشراف وغير الخاضع له باستخدام Scikit-learn.', 7, 'علم البيانات والذكاء الاصطناعي'),
(26, 'IT 371', 'أمن التطبيقات', 'يتناول هذا المقرر أمن تطبيقات الويب والبرمجيات، ويشمل ثغرات OWASP Top 10 وحقن SQL وXSS وأساليب الاختبار الأمني.', 6, 'الأمن السيبراني'),
(27, 'IT 471', 'إدارة الأمن الإلكتروني', 'يغطي هذا المقرر إدارة الأمن المعلوماتي في المؤسسات، ويشمل سياسات الأمن وإدارة الهوية والاستجابة للحوادث وأطر ISO 27001 وNIST.', 7, 'الأمن السيبراني'),
(28, 'IT 381', 'الحوسبة اللاسلكية والمحمولة', 'يتناول هذا المقرر تقنيات الشبكات اللاسلكية، ويشمل معايير الواي فاي وبلوتوث وشبكات الجيل الرابع والخامس وبروتوكولات الاتصال اللاسلكي.', 6, 'الشبكات وهندسة إنترنت الأشياء'),
(29, 'IT 481', 'مقدمة في إنترنت الأشياء', 'يُقدّم هذا المقرر مفاهيم إنترنت الأشياء وتطبيقاتها، ويشمل بروتوكولات MQTT وCoAP ومنصات إنترنت الأشياء وأجهزة Arduino وRaspberry Pi.', 7, 'الشبكات وهندسة إنترنت الأشياء'),
(30, 'CSC 113', 'برمجة حاسبات 2', 'امتداد لمقرر برمجة الحاسبات 1، يتناول البرمجة كائنية التوجه OOP ويشمل الفصائل والكائنات والوراثة والتعددية الشكلية والتغليف.', 4, 'عام'),
(31, 'SLMU 107', 'أخلاقيات المهنة', 'يتناول هذا المقرر أخلاقيات العمل المهني والقيم الإسلامية في بيئة العمل التقني، ويشمل مفاهيم النزاهة والمسؤولية المهنية وآداب التعامل.', 3, 'عام'),
(32, 'SLMU 100', 'دراسات في السيرة النبوية', 'يتناول هذا المقرر السيرة النبوية الشريفة ودروسها وعبرها، ويهدف إلى تعميق الارتباط بالنبي محمد ﷺ والاقتداء بسيرته في الحياة.', 3, 'عام'),
(33, 'QURAN 100', 'القرآن الكريم', 'يهدف هذا المقرر إلى تعزيز تلاوة القرآن الكريم وحفظه وفهم معانيه، ويشمل أحكام التجويد والتفسير الإجمالي لبعض السور.', 3, 'عام'),
(34, 'SLMU 101', 'أصول الثقافة الإسلامية', 'يتناول هذا المقرر أسس الثقافة الإسلامية ومصادرها، ويشمل العقيدة الإسلامية والفقه والسيرة النبوية وأثر الإسلام في الحضارة الإنسانية.', 3, 'عام'),
(35, 'SLMU 102', 'الأسرة في الإسلام', 'يتناول هذا المقرر نظام الأسرة في الإسلام وأحكامها، ويشمل الزواج والطلاق وحقوق الزوجين والأبناء ودور الأسرة في بناء المجتمع.', 3, 'عام'),
(36, 'SLMU 108', 'قضايا معاصرة', 'يتناول هذا المقرر القضايا المعاصرة من منظور إسلامي واجتماعي وتقني، ويشمل التحديات الأخلاقية في العصر الرقمي.', 4, 'عام'),
(37, 'SLMU 103', 'النظام الاقتصادي الإسلامي', 'يتناول هذا المقرر مبادئ الاقتصاد الإسلامي وأحكامه، ويشمل البيع والشراء والربا والزكاة والمعاملات المالية الإسلامية المعاصرة.', 4, 'عام'),
(38, 'SLMU 104', 'النظام السياسي الإسلامي', 'يتناول هذا المقرر مبادئ النظام السياسي الإسلامي، ويشمل مفهوم الشورى والعدل والحكم في الإسلام ومقارنته بالأنظمة المعاصرة.', 4, 'عام'),
(39, 'SLMU 105', 'حقوق الإنسان', 'يتناول هذا المقرر حقوق الإنسان من منظور إسلامي وإنساني، ويشمل الحقوق الأساسية للفرد في الإسلام ومقارنتها بالمواثيق الدولية.', 4, 'عام'),
(40, 'SLMU 106', 'الفقه الطبي', 'يتناول هذا المقرر الأحكام الفقهية المتعلقة بالمجال الطبي والصحي، ويشمل أخلاقيات الطب الإسلامية والقضايا الفقهية المعاصرة.', 4, 'عام'),
(41, 'SLMU 109', 'المرأة ودورها التنموي', 'يتناول هذا المقرر مكانة المرأة في الإسلام ودورها في التنمية، ويشمل حقوقها وواجباتها ومشاركتها في المجالات المختلفة.', 4, 'عام'),
(42, 'IT 479', 'التدريب الميداني', 'تدريب عملي في بيئة عمل حقيقية لمدة فصل دراسي، تطبّق الطالبة فيه المهارات المكتسبة في التخصص وتكتسب خبرة عملية في سوق العمل.', 7, 'عام'),
(43, 'IT 462', 'أنظمة البيانات الضخمة', 'يتناول هذا المقرر معالجة البيانات الضخمة، ويشمل إطارات Hadoop وSpark وتقنيات التخزين الموزع وأنابيب معالجة البيانات في بيئات الإنتاج.', 7, 'علم البيانات والذكاء الاصطناعي'),
(44, 'IT 465', 'تحليل البيانات وتمثيلها', 'يتناول هذا المقرر تقنيات التحليل المتقدم وتصور البيانات، ويشمل مكتبات Matplotlib وSeaborn وPlotly وبناء لوحات البيانات التفاعلية.', 8, 'علم البيانات والذكاء الاصطناعي'),
(45, 'IT 466', 'مواضيع مختارة في علم البيانات والذكاء الاصطناعي', 'يتناول هذا المقرر موضوعات متقدمة في علم البيانات والذكاء الاصطناعي، مثل الذكاء الاصطناعي التوليدي ونماذج اللغة الكبيرة LLM.', 8, 'علم البيانات والذكاء الاصطناعي'),
(46, 'IT 467', 'الذكاء الاصطناعي المتقدم', 'يتناول هذا المقرر موضوعات متقدمة في الذكاء الاصطناعي، ويشمل التخطيط الآلي والأنظمة الذكية المتعددة العوامل والاستدلال الاحتمالي.', 8, 'علم البيانات والذكاء الاصطناعي'),
(47, 'IT 468', 'الرؤية الحاسوبية التطبيقية', 'يتناول هذا المقرر تطبيقات الرؤية الحاسوبية، ويشمل معالجة الصور والتعرف على الأنماط والشبكات التلافيفية CNN باستخدام OpenCV وTensorFlow.', 8, 'علم البيانات والذكاء الاصطناعي'),
(48, 'IT 469', 'تقنيات اللغات البشرية', 'يتناول هذا المقرر معالجة اللغات الطبيعية NLP، ويشمل تحليل النصوص والنماذج اللغوية والترجمة الآلية وتطبيقات الذكاء الاصطناعي التوليدي.', 8, 'علم البيانات والذكاء الاصطناعي'),
(49, 'IT 482', 'أجهزة الاستشعار والشبكات المخصصة', 'يتناول هذا المقرر أنواع أجهزة الاستشعار وشبكات WSN، وبروتوكولات الاتصال المخصصة لإنترنت الأشياء وتطبيقاتها الصناعية.', 8, 'الشبكات وهندسة إنترنت الأشياء'),
(50, 'IT 483', 'خدمات وتطبيقات إنترنت الأشياء', 'يتناول هذا المقرر تصميم وبناء تطبيقات إنترنت الأشياء، ويشمل المنصات السحابية وإدارة البيانات وتطوير الخدمات الذكية المتكاملة.', 8, 'الشبكات وهندسة إنترنت الأشياء'),
(51, 'IT 484', 'الحوسبة السحابية', 'يتناول هذا المقرر مفاهيم الحوسبة السحابية ونماذج IaaS وPaaS وSaaS والأمن السحابي وإدارة الموارد في بيئات AWS وAzure.', 8, 'الشبكات وهندسة إنترنت الأشياء'),
(52, 'IT 485', 'أساسيات الروبوتات', 'يتناول هذا المقرر مبادئ الروبوتات والأتمتة، ويشمل الحركة والتحكم وأجهزة الاستشعار والبرمجة باستخدام ROS وأنظمة التحكم المدمجة.', 8, 'الشبكات وهندسة إنترنت الأشياء'),
(53, 'IT 486', 'موضوعات مختارة في الشبكات وإنترنت الأشياء', 'يتناول هذا المقرر موضوعات متقدمة في الشبكات وإنترنت الأشياء، مثل الشبكات المعرّفة بالبرمجيات SDN وشبكات الجيل الخامس.', 8, 'الشبكات وهندسة إنترنت الأشياء'),
(54, 'IT 472', 'الجريمة الإلكترونية والعلوم الجنائية الرقمية', 'يتناول هذا المقرر التحقيق في الجرائم الرقمية وتحليل الأدلة الجنائية، ويشمل أدوات التحليل الجنائي والإجراءات القانونية للجرائم الإلكترونية.', 8, 'الأمن السيبراني'),
(55, 'IT 473', 'أمن الأنظمة', 'يتناول هذا المقرر تأمين أنظمة التشغيل وإدارة الثغرات، ويشمل التصلب الأمني ومراقبة السجلات والاستجابة للحوادث الأمنية.', 8, 'الأمن السيبراني'),
(56, 'IT 474', 'أمن الشبكات', 'يتناول هذا المقرر حماية الشبكات وتأمينها، ويشمل جدران الحماية وأنظمة كشف التسلل IDS/IPS وبروتوكولات VPN والشبكات الآمنة.', 8, 'الأمن السيبراني'),
(57, 'IT 475', 'التدقيق والمراجعة المعلوماتية', 'يتناول هذا المقرر تدقيق أنظمة المعلومات والامتثال للمعايير، ويشمل إطار COBIT ومعايير ISO 27001 وإجراءات المراجعة والتحقق.', 8, 'الأمن السيبراني'),
(58, 'IT 476', 'موضوعات مختارة في أمن المعلومات', 'يتناول هذا المقرر موضوعات متقدمة في أمن المعلومات، مثل اختبار الاختراق والتشفير المتقدم والأمن السحابي وتقنيات الدفاع الإلكتروني.', 8, 'الأمن السيبراني');

-- --------------------------------------------------------

--
-- Table structure for table `courseprerequisite`
--

CREATE TABLE `courseprerequisite` (
  `courseID` int(5) NOT NULL,
  `prerequisiteCourseID` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `courseprerequisite`
--

INSERT INTO `courseprerequisite` (`courseID`, `prerequisiteCourseID`) VALUES
(7, 2),
(6, 3),
(6, 4),
(8, 5),
(30, 5),
(15, 8),
(12, 10),
(29, 10),
(25, 11),
(26, 12),
(21, 14),
(24, 14),
(18, 15),
(19, 18),
(15, 22),
(27, 26),
(14, 30);

-- --------------------------------------------------------

--
-- Table structure for table `experience`
--

CREATE TABLE `experience` (
  `experienceID` int(10) NOT NULL,
  `experienceContent` text NOT NULL,
  `studyNote` varchar(255) NOT NULL DEFAULT '',
  `studentID` int(10) NOT NULL,
  `courseID` int(5) NOT NULL,
  `likeCount` int(10) DEFAULT '0',
  `dislikeCount` int(10) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `experience`
--

INSERT INTO `experience` (`experienceID`, `experienceContent`, `studyNote`, `studentID`, `courseID`, `likeCount`, `dislikeCount`) VALUES
(4, 'مقرر ممتع ومثري، تجربة جميلة. المقرر يحتاج تركيز في موضوع التشفير وإدارة المخاطر. أنصح بمراجعة OWASP قبل الاختبار.', '', 4, 12, 2, 0),
(12, 'مقرر IT 320 من أكثر المقررات اللي استفدت منها، يحاكي بيئة العمل الحقيقية ويحتاج تنظيم أكثر من حفظ.\r\n\r\nركزي على منهجية **Agile وScrum** ومخططات **UML** خصوصاً Use Case وClass Diagram لأنهم يجون في الاختبار. فيه جزء نظري مهم للمصطلحات، برفق ملخص للمصطلحات يساعدكم.', 'study_notes/note_6_1777376492.pdf', 6, 15, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reaction`
--

CREATE TABLE `reaction` (
  `reactionID` int(10) NOT NULL,
  `reactionType` varchar(10) NOT NULL,
  `studentID` int(10) NOT NULL,
  `experienceID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reaction`
--

INSERT INTO `reaction` (`reactionID`, `reactionType`, `studentID`, `experienceID`) VALUES
(8, 'like', 5, 4),
(9, 'like', 6, 4);

-- --------------------------------------------------------

--
-- Table structure for table `resource`
--

CREATE TABLE `resource` (
  `resourceID` int(10) NOT NULL,
  `resourceTitle` varchar(100) NOT NULL,
  `resourceLink` varchar(255) NOT NULL,
  `courseID` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `resource`
--

INSERT INTO `resource` (`resourceID`, `resourceTitle`, `resourceLink`, `courseID`) VALUES
(1, 'مقدمة في تقنية المعلومات - YouTube', 'https://www.youtube.com/results?search_query=introduction+to+information+technology+arabic', 2),
(2, 'الفيزياء لتقنية المعلومات - YouTube', 'https://www.youtube.com/results?search_query=physics+for+computer+science', 3),
(3, 'الرياضيات المحددة - YouTube', 'https://www.youtube.com/results?search_query=discrete+mathematics+arabic', 4),
(4, 'برمجة حاسبات 1 - W3Schools Java', 'https://www.w3schools.com/java/', 5),
(5, 'كتاب أخلاقيات المهنة', 'uploads/resources/res_69f08ca0774a7.pdf', 31),
(6, 'كتاب دراسات في السيرة النبوية', 'uploads/resources/res_69f08cc3ad79f.pdf', 32),
(7, 'القرآن الكريم - موقع الجامعة', 'https://www.ksu.edu.sa/', 33),
(8, 'أصول الثقافة الإسلامية - موقع الجامعة', 'https://www.ksu.edu.sa/', 34),
(9, 'الأسرة في الإسلام - موقع الجامعة', 'https://www.ksu.edu.sa/', 35),
(10, 'تنظيم وعمارة الحاسبات - YouTube', 'https://www.youtube.com/results?search_query=computer+organization+architecture+arabic', 6),
(11, 'مبادئ قواعد البيانات - ملف المقرر', 'uploads/resources/res_69f083e339644.pdf', 7),
(12, 'تصميم تجربة المستخدم - Nielsen Norman', 'https://www.nngroup.com/articles/definition-user-experience/', 8),
(13, 'برمجة حاسبات 2 - W3Schools Java OOP', 'https://www.w3schools.com/java/java_oop.asp', 30),
(14, 'كتاب قضايا معاصرة', 'uploads/resources/res_69f08d50956df.pdf', 36),
(15, 'النظام الاقتصادي الإسلامي - موقع الجامعة', 'https://www.ksu.edu.sa/', 37),
(16, 'النظام السياسي الإسلامي - موقع الجامعة', 'https://www.ksu.edu.sa/', 38),
(17, 'حقوق الإنسان - موقع الجامعة', 'https://www.ksu.edu.sa/', 39),
(18, 'الفقه الطبي - موقع الجامعة', 'https://www.ksu.edu.sa/', 40),
(19, 'كتاب المرأة ودورها التنموي', 'uploads/resources/res_69f08ceb58907.pdf', 41),
(20, 'مبادئ شبكات الحاسب - Cisco NetAcad', 'https://www.netacad.com/courses/networking', 10),
(21, 'تنقيب البيانات - Towards Data Science', 'https://towardsdatascience.com/data-mining', 11),
(22, 'أمن المعلومات - OWASP', 'https://owasp.org/www-project-top-ten/', 12),
(23, 'هندسة تطبيقات الويب - MDN Web Docs', 'uploads/resources/res_69f08bc4dca75.pdf', 13),
(24, 'تراكيب البيانات - GeeksforGeeks', 'https://www.geeksforgeeks.org/data-structures/', 14),
(25, 'كتاب المقرر', 'uploads/resources/res_69f08c39892c3.pdf', 15),
(26, 'تقنيات الويب المتقدمة - ملف المقرر', 'uploads/resources/res_69f0846336f6d.pdf', 22),
(27, 'الجبر الخطي - Khan Academy', 'https://www.khanacademy.org/math/linear-algebra', 23),
(28, 'نظم التشغيل - ملف المقرر', 'uploads/resources/res_69f081dd1a3ac.pdf', 24),
(29, 'مبادئ علم البيانات - Kaggle Learn', 'https://www.kaggle.com/learn', 21),
(30, 'أمن التطبيقات - PortSwigger Web Security', 'https://portswigger.net/web-security', 26),
(31, 'الحوسبة اللاسلكية - IEEE Wireless', 'https://www.ieee.org/topics/wireless-communications.html', 28),
(32, 'إدارة مشاريع تقنية المعلومات - PMI', 'https://www.pmi.org/', 16),
(33, 'أساسيات الذكاء الاصطناعي - Google AI', 'https://ai.google/education/', 17),
(34, 'مشروع تخرج 1 - IEEE Xplore', 'https://ieeexplore.ieee.org/', 18),
(35, 'التدريب الميداني - موقع الجامعة', 'https://www.ksu.edu.sa/', 42),
(36, 'تعلم الآلة التطبيقي - Scikit-learn', 'https://scikit-learn.org/stable/', 25),
(37, 'أنظمة البيانات الضخمة - Apache Spark', 'https://spark.apache.org/docs/latest/', 43),
(38, 'إدارة الأمن الإلكتروني - NIST Framework', 'https://www.nist.gov/cyberframework', 27),
(39, 'إنترنت الأشياء - Arduino Tutorials', 'https://www.arduino.cc/en/Tutorial/HomePage', 29),
(40, 'مشروع تخرج 2 - IEEE Xplore', 'https://ieeexplore.ieee.org/', 19),
(41, 'ريادة الأعمال - Y Combinator Startup School', 'https://www.startupschool.org/', 20),
(42, 'تحليل البيانات وتمثيلها - Matplotlib', 'https://matplotlib.org/stable/tutorials/', 44),
(43, 'مواضيع الذكاء الاصطناعي - Hugging Face', 'https://huggingface.co/learn', 45),
(44, 'الذكاء الاصطناعي المتقدم - Stanford AI', 'https://ai.stanford.edu/', 46),
(45, 'الرؤية الحاسوبية - OpenCV Tutorials', 'https://docs.opencv.org/4.x/d9/df8/tutorial_root.html', 47),
(46, 'تقنيات اللغات البشرية - NLTK', 'https://www.nltk.org/', 48),
(47, 'أجهزة الاستشعار - IEEE Sensors', 'https://ieee-sensors.org/', 49),
(48, 'خدمات إنترنت الأشياء - AWS IoT', 'https://aws.amazon.com/iot/', 50),
(49, 'الحوسبة السحابية - AWS Training', 'https://aws.amazon.com/training/', 51),
(50, 'أساسيات الروبوتات - ROS Documentation', 'https://docs.ros.org/', 52),
(51, 'موضوعات الشبكات - Cisco Learning', 'https://learningnetwork.cisco.com/', 53),
(52, 'الجرائم الإلكترونية - SANS Digital Forensics', 'https://www.sans.org/digital-forensics-incident-response/', 54),
(53, 'أمن الأنظمة - CIS Benchmarks', 'https://www.cisecurity.org/cis-benchmarks/', 55),
(54, 'أمن الشبكات - Cisco Security', 'https://www.cisco.com/c/en/us/products/security/', 56),
(55, 'التدقيق المعلوماتي - ISACA', 'https://www.isaca.org/', 57),
(56, 'موضوعات أمن المعلومات - OWASP', 'https://owasp.org/', 58);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(10) NOT NULL,
  `studentName` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `studentName`, `email`, `password`) VALUES
(4, 'لمى', 'alhamed@gmail.com', '$2y$10$5c1mohIxpF8HFtD5DhdD3OhGTu4sndIECSEE142HpakfpPboSXwr6'),
(5, 'ساره', 'sara@gmail.com', '$2y$10$LUrssLfDEAliz/DuwefiK.8BbNc6Us/u3tsQv3mW77Oo1ZZjsg/WS'),
(6, 'لانا', 'lanaalmulhem@gmail.com', '$2y$10$aNr88opMgXYrSAn1w4mWO.hmuzvpUGH8juIUwiHaewEOnjBOnFNza');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`courseID`);

--
-- Indexes for table `courseprerequisite`
--
ALTER TABLE `courseprerequisite`
  ADD PRIMARY KEY (`courseID`,`prerequisiteCourseID`),
  ADD KEY `prerequisiteCourseID` (`prerequisiteCourseID`);

--
-- Indexes for table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`experienceID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `courseID` (`courseID`);

--
-- Indexes for table `reaction`
--
ALTER TABLE `reaction`
  ADD PRIMARY KEY (`reactionID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `experienceID` (`experienceID`);

--
-- Indexes for table `resource`
--
ALTER TABLE `resource`
  ADD PRIMARY KEY (`resourceID`),
  ADD KEY `courseID` (`courseID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `courseID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `experience`
--
ALTER TABLE `experience`
  MODIFY `experienceID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reaction`
--
ALTER TABLE `reaction`
  MODIFY `reactionID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `resource`
--
ALTER TABLE `resource`
  MODIFY `resourceID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courseprerequisite`
--
ALTER TABLE `courseprerequisite`
  ADD CONSTRAINT `cp_fk1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cp_fk2` FOREIGN KEY (`prerequisiteCourseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `exp_fk1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exp_fk2` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reaction`
--
ALTER TABLE `reaction`
  ADD CONSTRAINT `rea_fk1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rea_fk2` FOREIGN KEY (`experienceID`) REFERENCES `experience` (`experienceID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resource`
--
ALTER TABLE `resource`
  ADD CONSTRAINT `res_fk1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
