/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.Drawing;
using System.IO;
using System.Windows.Forms;
using Microsoft.Web.WebView2.Core;

namespace HappyGymSystem
{
    public partial class Form1 : Form
    {
        private Panel topBar;
        private Timer mouseTimer;

        public Form1()
        {
            InitializeComponent();
            SetupWindow();
            CreateTopBar();
            InitializeWebView();
            SetupMouseTracking();
        }

        private void SetupWindow()
        {
            this.FormBorderStyle = FormBorderStyle.None;
            this.MaximizedBounds = Screen.PrimaryScreen.WorkingArea;
            this.WindowState = FormWindowState.Maximized;

            try
            {
                string iconPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "logo.ico");
                if (File.Exists(iconPath))
                {
                    this.Icon = new Icon(iconPath);
                }
            }
            catch { }
        }

        private void CreateTopBar()
        {
            topBar = new Panel
            {
                Dock = DockStyle.Top,
                Height = 35,
                BackColor = Color.FromArgb(20, 20, 22),
                Visible = false
            };

            Button btnClose = new Button
            {
                Text = "✕",
                Dock = DockStyle.Right,
                Width = 45,
                FlatStyle = FlatStyle.Flat,
                ForeColor = Color.White,
                BackColor = Color.Transparent,
                Cursor = Cursors.Hand
            };
            btnClose.FlatAppearance.BorderSize = 0;
            btnClose.Click += (s, e) => Application.Exit();

            Button btnMinimize = new Button
            {
                Text = "—",
                Dock = DockStyle.Right,
                Width = 45,
                FlatStyle = FlatStyle.Flat,
                ForeColor = Color.White,
                BackColor = Color.Transparent,
                Cursor = Cursors.Hand
            };
            btnMinimize.FlatAppearance.BorderSize = 0;
            btnMinimize.Click += (s, e) => this.WindowState = FormWindowState.Minimized;

            Label lblTitle = new Label
            {
                Text = "  ⚡ Happy Gym PRO",
                ForeColor = Color.FromArgb(245, 158, 11),
                Font = new Font("Segoe UI", 10, FontStyle.Bold),
                Dock = DockStyle.Left,
                AutoSize = false,
                Width = 220,
                TextAlign = ContentAlignment.MiddleLeft,
                Padding = new Padding(10, 0, 0, 0)
            };

            topBar.Controls.Add(lblTitle);
            topBar.Controls.Add(btnMinimize);
            topBar.Controls.Add(btnClose);

            this.Controls.Add(topBar);
        }

        async void InitializeWebView()
        {
            var webView = new Microsoft.Web.WebView2.WinForms.WebView2();
            webView.Dock = DockStyle.Fill;
            this.Controls.Add(webView);
            webView.BringToFront();
            topBar.BringToFront();

            await webView.EnsureCoreWebView2Async(null);
            webView.CoreWebView2.Navigate("http://localhost/happymealplanner/auth/login.php");
        }

        private void SetupMouseTracking()
        {
            mouseTimer = new Timer();
            mouseTimer.Interval = 100;
            mouseTimer.Tick += (s, e) =>
            {
                int cursorY = Cursor.Position.Y;

                if (cursorY <= 5 && !topBar.Visible)
                {
                    topBar.Visible = true;
                }
                else if (cursorY > 40 && topBar.Visible)
                {
                    topBar.Visible = false;
                }
            };
            mouseTimer.Start();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
        }
    }
}