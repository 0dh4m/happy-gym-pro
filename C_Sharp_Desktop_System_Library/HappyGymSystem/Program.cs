/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <adamcherkaoui99@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace HappyGymSystem
{
    static class Program
    {
        /// <summary>
        /// The main entry point for the application.
        /// </summary>
        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new Form1());
        }
    }
}
