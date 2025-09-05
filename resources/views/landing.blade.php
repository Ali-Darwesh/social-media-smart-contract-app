<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق {{ config('app.name') }} | تواصل اجتماعي وعقود ذكية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        body { font-family: 'Tahoma', sans-serif; background-color: #f9f9f9; line-height: 1.8; overflow-x: hidden; }
        .hero {
            position: relative;
            background: linear-gradient(to right, #4f46e5, #6d28d9);
            color: white;
            padding: 120px 20px;
            text-align: center;
            overflow: hidden;
        }
        .hero img.logo { width: 150px; margin-bottom: 20px; }
        .floating-img { position: absolute; width: 180px; opacity: 0.3; animation: float 6s ease-in-out infinite; }
        .floating1 { top: 10%; left: 5%; animation-delay: 0s; }
        .floating2 { top: 20%; right: 10%; animation-delay: 2s; }
        .floating3 { bottom: 15%; left: 15%; animation-delay: 4s; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        .download-buttons a { display: inline-block; margin: 10px; }
        .download-buttons a button {
            border: none; border-radius: 10px; padding: 15px 30px;
            font-size: 18px; font-weight: bold; cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-android { background: #3DDC84; color: white; }
        .btn-windows { background: #0078d7; color: white; }
        .download-buttons a button:hover { transform: scale(1.05); }

        .video-section video { width: 100%; max-width: 800px; border-radius: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); }
        .features .icon { width: 90px; margin-bottom: 15px; }
        .screenshots img { width: 100%; border-radius: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.2); }

        .testimonials { background: #f1f1f1; padding: 60px 20px; }
        .testimonial-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin: 15px;
        }
        .testimonial-card img { width: 70px; border-radius: 50%; margin-bottom: 15px; }

        /* قسم الفريق */
        .team { background: #fff; padding: 60px 20px; }
        .team-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin: 15px; text-align: center;
            transition: transform 0.3s;
        }
        .team-card:hover { transform: translateY(-8px); }
        .team-card img { width: 120px; border-radius: 50%; margin-bottom: 15px; border: 4px solid #eee; }

        footer { background: #1f1f1f; color: #aaa; padding: 25px 0; }

        /* تأثير الأنيميشن عند التمرير على الصور */
        img:hover {
            transform: scale(1.1);
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body>

    <!-- الهيرو -->
    <section class="hero">
        <img src="https://cdn-icons-png.flaticon.com/512/906/906361.png" alt="Logo" class="logo" data-aos="zoom-in">
        <img src="https://cdn-icons-png.flaticon.com/512/1006/1006771.png" class="floating-img floating1" alt="">
        <img src="https://cdn-icons-png.flaticon.com/512/1006/1006772.png" class="floating-img floating2" alt="">
        <img src="https://cdn-icons-png.flaticon.com/512/1006/1006774.png" class="floating-img floating3" alt="">
        <div class="container">
            <h1 class="display-4 mb-3" data-aos="fade-up">مرحباً بك في <strong>{{ config('app.name') }}</strong></h1>
            <p class="lead fs-4" data-aos="fade-up" data-aos-delay="200">
                تطبيق ثوري يمزج بين **التواصل الاجتماعي السلس**، **المحادثات المشفرة بأمان فائق**، و**العقود الذكية** على البلوكشين لتجربة مبتكرة ومتكاملة.
            </p>
            <div class="download-buttons mt-4" data-aos="fade-up" data-aos-delay="400">
                <a href="{{ asset('download/n.png') }}" download><button class="btn-android">📱 تنزيل Android / iOS</button></a>
                <a href="{{ url('/download/app') }}" download><button class="btn-windows">💻 تنزيل Windows</button></a>
            </div>
        </div>
    </section>

    <!-- الفيديو التوضيحي -->
    <section class="video-section text-center py-5">
        <div class="container" data-aos="fade-up">
            <h2 class="mb-4">شاهد كيف يعمل التطبيق</h2>
            <video autoplay loop muted playsinline controls>
                <source src="https://videos.pexels.com/video-files/853889/853889-hd_1280_720_24fps.mp4" type="video/mp4">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>
        </div>
    </section>

    <section class="features text-center py-5">
        <div class="container">
          <h2 class="mb-5" data-aos="fade-up">ميزات تطبيقنا</h2>
          <div class="row g-4">
      
            <div class="col-md-4" data-aos="zoom-in">
              <img src="{{ asset('conversation.gif') }}" class="icon" alt="دردشة مشفرة">
              <h5>دردشة مشفرة</h5>
              <p>تواصل مع أصدقائك بشكل آمن وسريع بفضل تشفير المحادثات.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
              <img src="{{ asset('web-data.gif') }}" class="icon" alt="مشاركة المحتوى">
              <h5>مشاركة المحتوى</h5>
              <p>انشر صورك وفيديوهاتك وتفاعل مع منشورات الآخرين بسهولة.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
              <img src="{{ asset('smart-contract.gif') }}" class="icon" alt="عقود ذكية">
              <h5>عقود ذكية</h5>
              <p>وقّع وارفع عقودك على شبكات البلوكشين بأمان وشفافية.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="600">
              <img src="{{ asset('notification.gif') }}" class="icon" alt="إشعارات فورية">
              <h5>إشعارات فورية</h5>
              <p>احصل على إشعارات لحظية لكل جديد.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="800">
              <img src="{{ asset('smart-contract.gif') }}" class="icon" alt="لا للطرف الثالث">
              <h5>لا للطرف الثالث</h5>
              <p>أجري معاملاتك بسرعة وشفافية بدون أي تدخل خارجي.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="1000">
              <img src="{{ asset('profile.gif') }}" class="icon" alt="ملف شخصي متكامل">
              <h5>ملف شخصي متكامل</h5>
              <p>تحكّم بملفك الشخصي وصورك ومعلوماتك.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="1200">
              <img src="{{ asset('donation.gif') }}" class="icon" alt="لا للابتزاز">
              <h5>لا للابتزاز</h5>
              <p>وفر المال والوقت والجهد، معاملاتك في أمان.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="1400">
              <img src="{{ asset('likes.gif') }}" class="icon" alt="ردود فعل سريعة">
              <h5>ردود فعل سريعة</h5>
              <p>تفاعل بسرعة مع منشورات أصدقائك.</p>
            </div>
      
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="1600">
              <img src="{{ asset('data-safety.gif') }}" class="icon" alt="أمان محسن">
              <h5>أمان محسن</h5>
              <p>حماية متقدمة لبياناتك الشخصية والمحادثات.</p>
            </div>
      
          </div>
        </div>
      </section>
      

    <!-- لقطات التطبيق -->
    <section class="screenshots py-5 bg-light">
        <div class="container text-center">
            <h2 class="mb-4" data-aos="fade-up">لقطات من داخل التطبيق</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in">
                    <img src="https://images.pexels.com/photos/6078127/pexels-photo-6078127.jpeg" alt="screenshot">
                    <p class="mt-2">واجهة الرسائل المبسطة مع خاصية الردود السريعة.</p>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <img src="https://images.pexels.com/photos/5082570/pexels-photo-5082570.jpeg" alt="screenshot">
                    <p class="mt-2">صفحة البروفايل الشخصية لعرض نشاطاتك وصورك.</p>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                    <img src="https://images.pexels.com/photos/5081921/pexels-photo-5081921.jpeg" alt="screenshot">
                    <p class="mt-2">إنشاء عقود ذكية بسهولة وإدارتها في مكان واحد.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- آراء المستخدمين -->
    <section class="testimonials text-center">
        <div class="container">
            <h2 class="mb-5" data-aos="fade-up">ماذا يقول مستخدمونا؟</h2>
            <div class="row justify-content-center">
                <div class="col-md-3 testimonial-card" data-aos="flip-left">
                    <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="">
                    <p>"أفضل تطبيق للتواصل والعقود الذكية!"</p>
                    <strong>- أحمد</strong>
                </div>
                <div class="col-md-3 testimonial-card" data-aos="flip-left" data-aos-delay="200">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="">
                    <p>"سهل الاستخدام وآمن جداً، أنصح به للجميع!"</p>
                    <strong>- ليلى</strong>
                </div>
                <div class="col-md-3 testimonial-card" data-aos="flip-left" data-aos-delay="400">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="">
                    <p>"دمج رائع بين التواصل الاجتماعي وتقنية البلوكشين."</p>
                    <strong>- سامي</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- الفريق -->
    <section class="team text-center">
        <div class="container">
            <h2 class="mb-5" data-aos="fade-up" style="font-weight: 900; font-size: 2.8rem;">تعرف على فريق {{ config('app.name') }}</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 team-card" data-aos="zoom-in" style="padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); border-radius: 20px;">
                    <img src="https://randomuser.me/api/portraits/men/52.jpg" alt="Ismail" style="width: 180px; height: 180px; border: 6px solid #eee; border-radius: 50%; margin-bottom: 20px;">
                    <h5 style="font-weight: 900; font-size: 1.8rem;">إسماعيل</h5>
                    <p style="font-weight: 700; font-size: 1.2rem;">مهندس برمجيات ومتخصص في الذكاء الاصطناعي</p>
                </div>
                <div class="col-md-4 team-card" data-aos="zoom-in" data-aos-delay="200" style="padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); border-radius: 20px;">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Zain" style="width: 180px; height: 180px; border: 6px solid #eee; border-radius: 50%; margin-bottom: 20px;">
                    <h5 style="font-weight: 900; font-size: 1.8rem;">زين</h5>
                    <p style="font-weight: 700; font-size: 1.2rem;">محللة بيانات وخبيرة تجربة المستخدم</p>
                </div>
                <div class="col-md-4 team-card" data-aos="zoom-in" data-aos-delay="400" style="padding: 35px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); border-radius: 20px;">
                    <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Ali" style="width: 180px; height: 180px; border: 6px solid #eee; border-radius: 50%; margin-bottom: 20px;">
                    <h5 style="font-weight: 900; font-size: 1.8rem;">علي</h5>
                    <p style="font-weight: 700; font-size: 1.2rem;">مسؤول دعم فني ومشرف على الجودة</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center">
        <div class="container">
            <p>© {{ date('Y') }} {{ config('app.name') }} - جميع الحقوق محفوظة</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>