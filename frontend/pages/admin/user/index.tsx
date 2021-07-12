import React, { useEffect, useState } from 'react';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { User } from '../../../src/types/user';
import { PageComponent } from '../../../src/types/PageComponent';
import Head from 'next/head';
import {
  Button,
  Chip,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Tooltip,
  Typography,
} from '@material-ui/core';
import { useTranslation } from 'next-i18next';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import axiosClient from '../../../src/api';
import { makeStyles } from '@material-ui/core/styles';
import CheckIcon from '@material-ui/icons/Check';
import clsx from 'clsx';
import { useSnackbar } from 'notistack';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (!user || !user.admin) {
    return {
      redirect: {
        destination: '/',
        permanent: false,
      },
    };
  }

  return {
    props: {
      user,
      ...(await serverSideTranslations('de', ['common', 'admin'])),
    },
  };
}

const useStyles = makeStyles(() => ({
  userTypeChipExternal: {
    background: '#67f567',
  },
  userTypeChipLdap: {
    background: '#ff6c6c',
  },
  table: {
    minWidth: 650,
  },
  borderRadiusNone: {
    borderRadius: '0',
  },
  tableCellLessPadding: {
    padding: '8px 16px',
  },
  cursorDisable: {
    cursor: 'not-allowed',
  },
}));

const Home: PageComponent<Props> = (props: Props): JSX.Element => {
  const { t: tCommon } = useTranslation('common');
  const { t: tAdmin } = useTranslation('admin');

  const classes = useStyles();
  const [userList, setUserList] = useState<User[]>([]);
  const [actionPending, setActionPending] = useState<boolean>(false);

  const { enqueueSnackbar } = useSnackbar();

  const fetchUserList = React.useCallback(() => {
    axiosClient.get('/admin/user').then((response) => {
      setUserList(response.data.data);
    });
  }, [setUserList]);

  useEffect(() => {
    fetchUserList();
  }, [fetchUserList]);

  const toggleAdmin = React.useCallback(
    async (u: User) => {
      setActionPending(true);

      try {
        const response = await axiosClient.put('/admin/user/' + u.id, {
          admin: !u.admin,
        });
        setUserList((oldList) => [...oldList.map((el) => (el.id === u.id ? response.data.user : el))]);
      } catch (e) {
        enqueueSnackbar(
          e.response.data.code ? tCommon('responseCodes.' + e.response.data.code) : tCommon('unknownError'),
          {
            variant: 'error',
          },
        );
      }

      setActionPending(false);
    },
    [setUserList, setActionPending],
  );

  return (
    <>
      <Head>
        <title>
          {tAdmin('userControl.title')} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Typography gutterBottom variant="h4" component="h2">
          {tAdmin('userControl.title')}
        </Typography>
        <div className={classes.table}>
          <TableContainer component={Paper} className={classes.borderRadiusNone}>
            <Table className={classes.table} aria-label="simple table">
              <TableHead>
                <TableRow>
                  <TableCell>{tAdmin('userControl.columnId')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnLastName')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnFirstName')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnEmail')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnType')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnAdmin')}</TableCell>
                  <TableCell>{tAdmin('userControl.columnActions')}</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {userList &&
                  userList.map((u) => (
                    <TableRow key={u.id} hover>
                      <TableCell component="th" scope="row">
                        {u.id}
                      </TableCell>
                      <TableCell>{u.profile.lastName}</TableCell>
                      <TableCell>{u.profile.firstName}</TableCell>
                      <TableCell>{u.email}</TableCell>
                      <TableCell>
                        <Chip
                          size="small"
                          className={clsx(
                            classes.borderRadiusNone,
                            u.type === 'LDAP' && classes.userTypeChipLdap,
                            u.type === 'EXTERNAL' && classes.userTypeChipExternal,
                          )}
                          label={u.type}
                        />
                      </TableCell>
                      <TableCell className={classes.tableCellLessPadding}>{u.admin && <CheckIcon />}</TableCell>
                      <TableCell className={classes.tableCellLessPadding}>
                        <Tooltip
                          title={u.id === props.user.id ? tAdmin('userControl.errorCantChangeOwnAdminStatus') : ''}
                        >
                          <span className={u.id === props.user.id ? classes.cursorDisable : ''}>
                            <Button
                              variant="contained"
                              color="primary"
                              disabled={actionPending || u.id === props.user.id}
                              onClick={() => toggleAdmin(u)}
                            >
                              {u.admin
                                ? tAdmin('userControl.actionRemoveAdmin')
                                : tAdmin('userControl.actionMakeAdmin')}
                            </Button>
                          </span>
                        </Tooltip>
                      </TableCell>
                    </TableRow>
                  ))}
              </TableBody>
            </Table>
          </TableContainer>
        </div>
      </>
    </>
  );
};

Home.layout = 'main';
export default Home;
