import { AppBar, Button, Container, makeStyles, Toolbar, Typography, Menu, MenuItem } from '@material-ui/core';
import clsx from 'clsx';
import React from 'react';
import { ArrowDropDown } from '@material-ui/icons';
import { useRouter } from 'next/router';
import axiosClient from '../api';
import Link from 'next/link';
import { PageComponent } from '../types/PageComponent';
import { User } from '../types/user';

const useStyles = makeStyles((theme) => ({
  appBar: {
    zIndex: theme.zIndex.drawer + 1,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
  },
  leftNavItems: {
    marginLeft: theme.spacing(4),
  },
  spacing: {
    flexGrow: 1,
  },
  appBarSpacer: theme.mixins.toolbar,
  content: {
    flexGrow: 1,
    height: '100vh',
    overflow: 'auto',
  },
  container: {
    paddingTop: theme.spacing(4),
    paddingBottom: theme.spacing(4),
  },
}));

type Props = {
  children: React.ReactElement<unknown, PageComponent<unknown>>;
  user?: User;
};

export default function MainLayout(props: Props): JSX.Element {
  const classes = useStyles();
  const router = useRouter();

  const [anchorEl, setAnchorEl] = React.useState(null);
  const isMenuOpen = Boolean(anchorEl);

  const handleProfileMenuOpen = (event): void => {
    setAnchorEl(event.currentTarget);
  };

  const handleMenuClose = (): void => {
    setAnchorEl(null);
  };

  const handleProfileClick = async (): Promise<void> => {
    handleMenuClose();
    await router.push('/user/' + props.user.id);
  };

  const handleLogoutClick = async (): Promise<void> => {
    handleMenuClose();
    await axiosClient.post('/logout', {});
    await router.push('/');
  };

  return (
    <div>
      <AppBar position="absolute" className={clsx(classes.appBar)}>
        <Toolbar>
          <Typography component="h1" variant="h6" color="inherit" noWrap>
            Themenbörse
          </Typography>
          <div className={classes.leftNavItems}>
            <Link href="/">
              <Button color="inherit">Startseite</Button>
            </Link>
            {props.user.admin && (
              <Link href="/admin/user">
                <Button color="inherit">Benutzerverwaltung</Button>
              </Link>
            )}
          </div>

          <div className={classes.spacing}></div>
          {props.user && (
            <Button
              aria-label="account of current user"
              aria-controls={'user-profile-menu'}
              aria-haspopup="true"
              onClick={handleProfileMenuOpen}
              color="inherit"
            >
              {props.user.profile.firstName} {props.user.profile.lastName} <ArrowDropDown />
            </Button>
          )}
        </Toolbar>
      </AppBar>
      {props.user && (
        <Menu
          anchorEl={anchorEl}
          anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
          id={'user-profile-menu'}
          getContentAnchorEl={null}
          keepMounted
          transformOrigin={{ vertical: 'top', horizontal: 'right' }}
          open={isMenuOpen}
          onClose={handleMenuClose}
        >
          <MenuItem onClick={handleProfileClick}>Profil</MenuItem>
          <MenuItem onClick={handleLogoutClick}>Logout</MenuItem>
        </Menu>
      )}
      <main className={classes.content}>
        <div className={classes.appBarSpacer} />
        <Container maxWidth="lg" className={classes.container}>
          {props.children}
        </Container>
      </main>
    </div>
  );
}
